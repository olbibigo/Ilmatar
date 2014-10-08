<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;

class JobController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/job';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::JOB,
        'type'           => \Entities\Permission::ACCESS_READ
    );
    public static $CREDENTIALS = array(
        'role-change' => array (
            'type' => \Entities\Permission::ACCESS_READWRITE
        )
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
                        ->bind('job-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('job-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/edit', __CLASS__ . ":changeAction")
                        ->bind('job-change'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Delegates to base controller
        return $this->displayEditableJqPage(
            '\\Entities\\Job',
            $app['translator']->trans('Recorded jobs'),
            array(
                'title'   => $app['translator']->trans('Job list'),
                'buttons' => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_SAVE,
                            "title"  => $app['translator']->trans('Save selected job'),
                            "action" => array(
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => "saveJqGridRow()"
                            )
                        )
                    ),
                    $app
                )
            ),
            'job-load',
            'job-change',
            $app
        );
    }

    public function loadAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\Job',
                $request,
                $app
            )
        );
    }

    public function changeAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $this->changeJqPage(
            '\\Entities\\Job',
            $request,
            $app
        );
    }
}
