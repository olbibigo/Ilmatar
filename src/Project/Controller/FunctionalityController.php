<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Ilmatar\HelperFactory;
use Ilmatar\JqGrid;

class FunctionalityController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/functionality';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::ROLE,
        'type'          => \Entities\Permission::ACCESS_READ
    );
    public static $CREDENTIALS = array(
        'functionality-edit' => array (
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
                        ->bind('functionality-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('functionality-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/edit/{functionalityId}', __CLASS__ . ":editAction")
                        ->assert('functionalityId', '^-?\d*$')
                        ->value('functionalityId', JqGrid::ID_NEW_ENTITY)
                        ->bind('functionality-edit'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/export', __CLASS__ . ":exportAction")
                        ->bind('functionality-export'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Delegates to base controller
        return $this->displayJqPage(
            '\\Entities\\Functionality',
            $app['translator']->trans('Recorded functionalities'),
            array(
                'title'   => $app['translator']->trans('Functionality list'),
                'buttons' => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_EXPORT,
                            "title"  => $app['translator']->trans('Export functionalities'),
                            "action"    => array(
                                "data" => sprintf("exportGrid('%s')", $app['url_generator']->generate('functionality-export'))
                            )
                        )
                    ),
                    $app
                )
            ),
            'functionality-load',
            $app
        );
    }

    public function loadAction(Request $request, Application $app)
    {
        $repo = $app['orm.em']->getRepository('\\Entities\\Permission');
        $user = $app['security']->getToken()->getUser();

        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\Functionality',
                $request,
                $app,
                true,
                array(
                    '_locale'         => $request->get('_locale'),
                    'url_generator'   => $app['url_generator'],
                    'has_role_access' => $repo->isAllowedFunctionality($user, FunctionalityController::$DEFAULT_CREDENTIALS["functionality"])
                )
            )
        );
    }

    public function exportAction(Request $request, Application $app)
    {
         //Delegates to base controller
        return $this->exportJqPage(
            '\\Entities\\Functionality',
            $request,
            $app,
            array('_locale' => $request->get('_locale'))
        );
    }
}
