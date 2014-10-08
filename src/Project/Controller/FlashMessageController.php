<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;

class FlashMessageController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/flashmessage';

    public static $DEFAULT_CREDENTIALS = [
        'functionality' => \Entities\Functionality::FLASH_MESSAGE,
        'type'           => \Entities\Permission::ACCESS_READ
    ];
    public static $CREDENTIALS = [
        'flashmessage-change' => [
            'type' => \Entities\Permission::ACCESS_READWRITE
        ]
    ];

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
                        ->bind('flashmessage-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('flashmessage-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/edit', __CLASS__ . ":changeAction")
                        ->bind('flashmessage-change'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Delegates to base controller
        return $this->displayEditableJqPage(
            '\\Entities\\FlashMessage',
            $app['translator']->trans('Recorded flash messages'),
            [
                'title'   => $app['translator']->trans('Flash message list'),
                'buttons' => $this->setButtons(
                    [
                        [
                            "type"   => self::BUTTON_TYPE_SAVE,
                            "title"  => $app['translator']->trans('Save selected flash message'),
                            "action" => [
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => "saveJqGridRow()"
                            ]
                        ],
                        [
                            "type"   => self::BUTTON_TYPE_ADD,
                            "title"  => $app['translator']->trans('Add a flash message'),
                            "action" => [
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => "addJqGridRow()"
                            ]
                        ],
                        [
                            "type"   => self::BUTTON_TYPE_DELETE,
                            "title"  => $app['translator']->trans('Delete selected flash message'),
                            "action" => [
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => "deleteJqGridRow([])"
                            ]
                        ]
                    ],
                    $app
                )
            ],
            'flashmessage-load',
            'flashmessage-change',
            $app
        );
    }

    public function loadAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\FlashMessage',
                $request,
                $app
            )
        );
    }

    public function changeAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $this->changeJqPage(
            '\\Entities\\FlashMessage',
            $request,
            $app
        );
    }
}
