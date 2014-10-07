<?php
namespace Project\Controller;

use Ilmatar\Application;
use Ilmatar\BaseFrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;
use Ilmatar\TagManager;

/*
 * Should declare here only internal routes
 */
class InternalFrontController extends BaseFrontController
{
    //Uses a complicated prefix to prevent access from outside
    const ROUTE_PREFIX = '6B393ECF-2E55-4190-B57A-02761103905C';
    
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
        parent::setDefaultConfig(
            $controllers->get('/header/{username}', __CLASS__ . ":headerAction")
                        ->value('username', 'anonymous')
                        ->bind('internal_header'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->get('/footer/{username}', __CLASS__ . ":footerAction")
                        ->value('username', 'anonymous')
                        ->bind('internal_footer'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/push/{editCode}', __CLASS__ . ":editorialPushAction")
                        ->bind('editorial-push'),
            $app
        );

                                  
        return $controllers;
    }
    
    public function headerAction($username, Application $app)
    {
        return $app['twig']->render(
            'front/header.twig',
            ['username' => $username]
        );
    }
    
    public function footerAction($username, Application $app)
    {
        return $app['twig']->render(
            'front/footer.twig',
            ['username' => $username]
        );
    }
    
    public function editorialPushAction($editCode, Request $request, Application $app)
    {
        return new Response('');
    }
}
