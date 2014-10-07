<?php
namespace Project\Controller;

use Ilmatar\Application;
use Ilmatar\BaseFrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Ilmatar\HelperFactory;

class FrontController extends BaseFrontController
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
        parent::setDefaultConfig(
            $controllers->get('/', __CLASS__ . ":indexAction")
                        ->bind('homepage'),
            $app
        );
        return $controllers;
    }
    
    public function indexAction(Application $app)
    {
        return $app['twig']->render(
            'front/homepage.twig',
            [
                'title' => $app['translator']->trans('Front office homepage'),
                'metas' => [
                                [
                                    "name"    => "description",
                                    "content" => $app['translator']->trans("Front office homepage")
                                ]
                           ]
            ]
        );
    }
}
