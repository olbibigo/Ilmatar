<?php
namespace Ilmatar;

use Silex\Controller;
use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\ControllerProviderInterface;

abstract class BaseController implements ControllerProviderInterface
{
    /*
     * Default credentials for all routes
     * Can be overwitten into Controllers
     */
    public static $DEFAULT_CREDENTIALS = [
        'type'          => null,
        'functionality' => null
    ];
    /*
     * Credentials defined by route
     * Can be overwitten into Controllers
     */
    public static $CREDENTIALS = [];
    
    const PARAM_TOKEN_BIRTH = "csrfTokenBirth";
    //!! Parameters below are used in ModelWrite.js as attribute name
    const PARAM_TOKEN       = "csrfToken";

    protected $options = [];

    public function __construct(Application $app, array $options = [])
    {
        $this->options = $options;
    }

    public static function isTrue($array, $value)
    {
        return (isset($array[$value]) && (true === $array[$value]));
    }
    
    public static function isFalse($array, $value)
    {
        return !self::isTrue($array, $value);
    }

    protected function isValidToken($token, Application $app)
    {
        if (!$token
          || ($token != $app['session']->get(self::PARAM_TOKEN))
          || (time() - $app['session']->get(self::PARAM_TOKEN_BIRTH) >= $app['session.lifetime'])
        ) {
            return false;
        }
        //Reset token validity delay
        $app['session']->set(self::PARAM_TOKEN_BIRTH, time());
        return true;
    }

    protected function generateToken(Application $app)
    {
        $token = md5(uniqid());
        $app['session']->set(self::PARAM_TOKEN, $token);
        $app['session']->set(self::PARAM_TOKEN_BIRTH, time());
        return $token;
    }
    
    protected function get304Response($etag, Request $request, Application $app)
    {
        if (isset($app['http_cache'])) {
            $response = new \Symfony\Component\HttpFoundation\Response();
            $response->setETag($etag);
            $response->setPublic();
            if ($response->isNotModified($request)) {
                // return the 304 Response immediately
                return $response;
            } else {
                return false;
            }
        }
        return false;
    }

    protected function setMenu($menus, Request $request, Application $app)
    {
        if (!is_null($app["security"]->getToken())) {
            $app['menu.set']($menus, $request, $app);
        }
    }
    
    protected function setDefaultConfig(Controller $controller, Application $app)
    {
        $controller->value('_locale', $app['locale']);
        if ($app['https']) {
            $controller->requireHttps();
        }
        return $controller;
    }
}
