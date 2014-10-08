<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;

class NewsController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/news';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality'  => \Entities\Functionality::DASHBOARD, //Not NEWS
        'type'            => \Entities\Permission::ACCESS_READ
    );
    
    public static $CREDENTIALS = array(
        'news-change' => array (
            'functionality' => \Entities\Functionality::NEWS,
            'type'           => \Entities\Permission::ACCESS_READWRITE
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
                        ->bind('news-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('news-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/edit', __CLASS__ . ":changeAction")
                        ->bind('news-change'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Delegates to base controller
        return $this->displayEditableJqPage(
            '\\Entities\\News',
            $app['translator']->trans('Recorded news'),
            array(
                'title'   => $app['translator']->trans('News list'),
                'buttons' => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_SAVE,
                            "title"  => $app['translator']->trans('Save selected news'),
                            "action" => array(
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => "saveJqGridRow()"
                            )
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_ADD,
                            "title"  => $app['translator']->trans('Add a news'),
                            "action" => array(
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => "addJqGridRow()"
                            )
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_DELETE,
                            "title"  => $app['translator']->trans('Delete selected news'),
                            "action" => array(
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => "deleteJqGridRow([])"
                            )
                        )
                    ),
                    $app
                )
            ),
            'news-load',
            'news-change',
            $app
        );
    }

    public function loadAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\News',
                $request,
                $app
            )
        );
    }

    public function changeAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $this->changeJqPage(
            '\\Entities\\News',
            $request,
            $app
        );
    }
}
