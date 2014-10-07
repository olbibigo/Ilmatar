<?php
namespace Ilmatar;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;

/*
 * Declare here routes shared by all vhost
 */
class CommonController extends BaseController
{
    const ROUTE_PREFIX = '';
    
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
        $controllers->get('/translations/', __CLASS__ . ":translateAction")
                    ->bind('translations');
                    
        return $controllers;
    }
    
    public function translateAction(Request $request, Application $app)
    {
        $lg = $request->get("lg");
        $cb = $request->get("cb");
        return new Response(
            $cb . '(' . $app['gaufrette.filesystem']->read('/locales/' . $lg . '.json') . ');',
            200,
            ['Content-Type' => 'text/javascript']
        );
    }
}
