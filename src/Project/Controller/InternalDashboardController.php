<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;

/*
 * Should declare here only internal routes
 */
class InternalDashboardController extends BaseBackController
{
    //Uses a complicated prefix to prevent access from outside
    const ROUTE_PREFIX = '7B393ECF-3E55-5190-C57A-12761103905C';
    
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
            $controllers->get('/header/{landingPage}', __CLASS__ . ":headerAction")
                        ->bind('internal_header'),
            $app
        );
        
        parent::setDefaultConfig(
            $controllers->get('/footer/', __CLASS__ . ":footerAction")
                        ->bind('internal_footer'),
            $app
        );

        return $controllers;
    }
    
    public function headerAction($landingPage, Request $request, Application $app)
    {
        $nbInternalMails = 0;
        if (!is_null($app['security']->getToken())) {
            $nbInternalMails = $app['orm.em']->getRepository('\Entities\InternalMail')->count(
                [
                    'to'      => $app['security']->getToken()->getuser(),
                    'read_at' => null
                ]
            );
        }
        return $app['twig']->render(
            'back/header.twig',
            [
                'nbInternalMails' => $nbInternalMails,
                'landingPage'     => $landingPage
            ]
        );
    }
    
    public function footerAction(Request $request, Application $app)
    {
        return $app['twig']->render(
            'back/footer.twig'
        );
    }
}
