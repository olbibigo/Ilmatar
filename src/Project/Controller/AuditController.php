<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;
use Ilmatar\JqGrid;

class AuditController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/audit';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::LOG,
        'type'           => \Entities\Permission::ACCESS_READ
    );

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
            $controllers->get('/', __CLASS__ . ":displayAction")
                        ->bind('audit-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('audit-load'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);

        return $this->displayJqPage(
            '\\Entities\\Audit',
            $app['translator']->trans('Recorded changes'),
            ['title' => $app['translator']->trans('Database history')],
            'audit-load',
            $app
        );
    }
    
    public function loadAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\Audit',
                $request,
                $app
            )
        );
    }
}
