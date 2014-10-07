<?php
namespace Project\Controller;

use Ilmatar\Application;
use Ilmatar\HelperFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebserviceController extends BaseBackController
{
    const ROUTE_PREFIX       = 'webservice';
    const TOKEN_PARAM        = 'token';
    const ASSOCIATION_SUFFIX = '_id';
    const SEPARATOR          = ';';

    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';

    //CREDENTIALS are not in use here
    //All checks are done directly into action methods

    public function connect(\Silex\Application $app)
    {
        $app[__CLASS__] = $app->share(
            function () {
                return $this;
            }
        );
        $controllers = $app['controllers_factory'];
        /*
         * Route declarations
         */
        $controller = parent::setDefaultConfig(
            $controllers->get('/{entity}', __CLASS__ . ":getEntitiesAction")
                        ->bind('webservice-read-entities'),
            $app
        );
        if ($app['webservices']['https']) {
            $controller->requireHttps();
        }

        $controller = parent::setDefaultConfig(
            $controllers->get('/{entity}/{id}', __CLASS__ . ":getEntityAction")
                        ->assert('id', '\d+')
                        ->bind('webservice-read-entity'),
            $app
        );
        if ($app['webservices']['https']) {
            $controller->requireHttps();
        }

        $controller = parent::setDefaultConfig(
            $controllers->post('/{entity}', __CLASS__ . ":postEntityAction")
                        ->bind('webservice-create-entity'),
            $app
        );
        if ($app['webservices']['https']) {
            $controller->requireHttps();
        }

        $controller = parent::setDefaultConfig(
            $controllers->put('/{entity}/{id}', __CLASS__ . ":putEntityAction")
                        ->assert('id', '\d+')
                        ->bind('webservice-update-entity'),
            $app
        );
        if ($app['webservices']['https']) {
            $controller->requireHttps();
        }

        $controller = parent::setDefaultConfig(
            $controllers->delete('/{entity}/{id}', __CLASS__ . ":deleteEntityAction")
                        ->assert('id', '\d+')
                        ->bind('webservice-delete-entity'),
            $app
        );
        if ($app['webservices']['https']) {
            $controller->requireHttps();
        }

        //Add web services here

        return $controllers;
    }

    public function getEntitiesAction($entity, Request $request, Application $app)
    {
        $cleanEntity = $this->checkRights($entity, $request, $app, self::HTTP_METHOD_GET);
        if ($cleanEntity instanceof Response) {
            return $cleanEntity;
        }
        $repo = $app['orm.em']->getRepository($cleanEntity);
        return $app->json(
            HelperFactory::build('ObjectHelper')->getJsonFromObjects(
                $repo->findAll(),
                $repo->getFieldAndAssociationNames()
            )
        );
    }

    public function getEntityAction($entity, $id, Request $request, Application $app)
    {
        $cleanEntity  = $this->checkRights($entity, $request, $app, self::HTTP_METHOD_GET);
        if ($cleanEntity instanceof Response) {
            return $cleanEntity;
        }
        $obj = $this->getObject($cleanEntity, $id, $app);
        return $app->json(
            HelperFactory::build('ObjectHelper')->getJsonFromObjects(
                [$obj],
                $app['orm.em']->getRepository($cleanEntity)->getFieldAndAssociationNames()
            )
        );
    }

    public function postEntityAction($entity, Request $request, Application $app)
    {
        $cleanEntity = $this->checkRights($entity, $request, $app, self::HTTP_METHOD_POST);
        if ($cleanEntity instanceof Response) {
            return $cleanEntity;
        }
        $values = $app['orm.em']->getRepository($cleanEntity)->unformatAll(
            $request->request->all(),
            $app['translator'],
            [\Ilmatar\JqGrid::IS_WEBSERVICE => true]
        );
        try {
            $obj = new $cleanEntity($values);
            $app['orm.em']->persist($obj);
            $app['orm.em']->flush();
        } catch (\Exception $e) {
            return new Response(
                $e->getMessage(),
                403
            );
        }
        return new Response($obj->getId());
    }

    public function putEntityAction($entity, $id, Request $request, Application $app)
    {
        $cleanEntity = $this->checkRights($entity, $request, $app, self::HTTP_METHOD_PUT);
        if ($cleanEntity instanceof Response) {
            return $cleanEntity;
        }
        $obj    = $this->getObject($cleanEntity, $id, $app);
        $values = $app['orm.em']->getRepository($cleanEntity)->unformatAll(
            $request->request->all(),
            $app['translator'],
            [\Ilmatar\JqGrid::IS_WEBSERVICE => true]
        );
        try {
            $obj->fill($values);
            $app['orm.em']->persist($obj);
            $app['orm.em']->flush();
        } catch (\Exception $e) {
            return new Response(
                $e->getMessage(),
                403
            );
        }
        return new Response('OK');
    }

    public function deleteEntityAction($entity, $id, Request $request, Application $app)
    {
        $cleanEntity = $this->checkRights($entity, $request, $app, self::HTTP_METHOD_DELETE);
        if ($cleanEntity instanceof Response) {
            return $cleanEntity;
        }
        $obj = $this->getObject($cleanEntity, $id, $app);
        try {
            $app['orm.em']->remove($obj);
            $app['orm.em']->flush();
        } catch (\Exception $e) {
            return new Response(
                $e->getMessage(),
                403
            );
        }
        return new Response('OK');
    }


    protected function checkRights($entity, Request $request, Application $app, $httpMethodToCheck)
    {
        $ret = $this->checkActive($app);
        if ($ret instanceof Response) {
            return $ret;
        }
        $credential = $this->checkSignature($request, $app, $httpMethodToCheck);
        if ($credential instanceof Response) {
            return $credential;
        }
        $ret = $this->checkIPAndDomain($request, $app, $credential);
        if ($ret instanceof Response) {
            return $ret;
        }

        return $this->checkEntity($entity, $app, $httpMethodToCheck);
    }

    protected function checkActive(Application $app)
    {
        if (!$app['webservices']['isActive']) {
            return new Response(
                $app['translator']->trans("Not active service"),
                403
            );
        }
    }

    protected function checkSignature(Request $request, Application $app, $httpMethodToCheck)
    {
        //Expected token is base64_encode($publicKey
        //. self::SEPARATOR . $salt . self::SEPARATOR ;
        //hash_hmac('sha256', $request->getPathInfo(), $salt . $sharedKey))
        $token = (self::HTTP_METHOD_GET == $httpMethodToCheck) ? $request->query->get(self::TOKEN_PARAM) : $request->request->get(self::TOKEN_PARAM);
        if (is_null($token)) {
            return new Response(
                $app['translator']->trans("Missing token"),
                403
            );
        }
        $token = explode(self::SEPARATOR, base64_decode($token));
        if (count($token) != 3) {
            return new Response(
                $app['translator']->trans("Invalid token structure"),
                403
            );
        }

        list($publicKey, $salt, $signature) = $token;

        foreach ($app['webservices']['credentials']['credential'] as $credential) {
            if ($credential['public.key'] == $publicKey) {
                if ($signature != hash_hmac('sha256', $request->getPathInfo(), $salt . $credential['shared.key'])) {
                    return new Response(
                        $app['translator']->trans("Wrong signature"),
                        403
                    );
                }
                return $credential;
            }
        }
        return new Response(
            $app['translator']->trans("Invalid public key"),
            403
        );
    }

    protected function checkIPAndDomain(Request $request, Application $app, Array $credential)
    {
        $ip         = $request->getClientIp();
        $allowedIps = explode(self::SEPARATOR, $credential['allowed.ips']);
        $isFound    = false;
        foreach ($allowedIps as $allowedIp) {
            if (1 === preg_match('/^' . $allowedIp . '$/' , $ip)) {
                $isFound = true;
                break;
            }
        }
        if (!$isFound) {
            return new Response(
                $app['translator']->trans("Not allowed IP"),
                403
            );
        }

        $domain         = $request->getBaseUrl();
        $allowedDomains = explode(self::SEPARATOR, $credential['allowed.domains']);
        foreach ($allowedDomains as $allowedDomain) {
            if (1 === preg_match('/^' . $allowedDomain . '$/' , $domain)) {
                return true;
            }
        }
        return new Response(
            $app['translator']->trans("Not allowed domain"),
            403
        );
    }

    protected function checkEntity($entity, Application $app, $httpMethodToCheck)
    {
        $meta = $app['orm.em']->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $name = $m->getName();
            if (0 == strcasecmp('Entities\\' . $entity, $name)) {
                if (!in_array($httpMethodToCheck, $name::$allowedHttpMethodForWebservice)) {
                    return new Response(
                        $app['translator']->trans("Not allowed method for entity"),
                        403
                    );
                }
                return $name;
            }
        }
        return new Response(
            $app['translator']->trans("Unknown entity"),
            403
        );
    }

    protected function getObject($entity, $id, Application $app)
    {
        $repo = $app['orm.em']->getRepository($entity);
        $obj  = $repo->find($id);
        if (is_null($obj)) {
            return new Response(
                $app['translator']->trans("Unknown object"),
                403
            );
        }
        return $obj;
    }
}
