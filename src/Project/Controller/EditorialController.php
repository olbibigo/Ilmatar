<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;
use Ilmatar\JqGrid;
use Project\Form\Editorial;

class EditorialController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/editorial';

    public static $DEFAULT_CREDENTIALS = [
        'functionality' => \Entities\Functionality::EDITORIAL,
        'type'           => \Entities\Permission::ACCESS_READ
    ];
    public static $CREDENTIALS = [
        'editorial-edit' => [
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
                        ->bind('editorial-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('editorial-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/edit/{editId}', __CLASS__ . ":editAction")
                        ->assert('editId', '^-?\d*$')
                        ->value('editId', JqGrid::ID_NEW_ENTITY)
                        ->bind('editorial-edit'),
            $app
        );
        
        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Delegates to base controller
        return $this->displayJqPage(
            '\\Entities\\Editorial',
            $app['translator']->trans('Recorded editorial pushs'),
            [
                'title'   => $app['translator']->trans('Editorial push list'),
                'buttons' => $this->setButtons(
                    [
                        [
                            "type"   => self::BUTTON_TYPE_EDIT,
                            "title"  => $app['translator']->trans('Edit selected editorial push'),
                            "action" => [
                                "data" => $app['url_generator']->generate('editorial-edit', ['editId' => JqGrid::PARAM_URL_ROW_ID])
                            ]
                        ]
                    ],
                    $app
                )
            ],
            'editorial-load',
            $app
        );
    }

    public function loadAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\Editorial',
                $request,
                $app
            )
        );
    }

    public function editAction($editId, Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);

        $buttons = $this->setButtons(
            [
                [
                    "type"   => self::BUTTON_TYPE_LIST,
                    "title"  => $app['translator']->trans('Back to editorial push list'),
                    "action" => [
                        "data" => $app['url_generator']->generate('editorial-display')
                    ]
                ],
                [
                    "type"   => self::BUTTON_TYPE_SAVE,
                    "title"  => $app['translator']->trans('Save editorial push'),
                ]
            ],
            $app
        );
        
        $push = $app['orm.em']->find('\\Entities\\Editorial', $editId);
        if (!$push instanceof \Entities\Editorial) {
            $app->abort(
                403,
                sprintf(
                    $app['translator']->trans("Object %s does not exist."),
                    $editId
                )
            );
        }

        $editForm = $app['form.factory']->create(
            new \Project\Form\EditorialType(
                [
                    'action' => $app['url_generator']->generate(
                        'editorial-edit',
                        [
                            'editId' => $editId
                        ]
                    )
                ]
            ),
            $push
        );
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()) {
            if ($editForm->isValid()) {
                $oper = $request->get(JqGrid::JQGRID_KEY_OPER);
                switch($oper) {
                    case JqGrid::JQGRID_ACTION_UPDATE:
                        try {
                            $app['orm.em']->persist($push);
                            $app['orm.em']->flush();
                            $app['notification'](
                                sprintf(
                                    $app['translator']->trans("Requested operation executed successfully (id: %s)."),
                                    $push->getId()
                                ),
                                'success'
                            );
                            //Redirects to list
                            return $app->redirect($app['url_generator']->generate('editorial-display'));
                        } catch (\Exception $e) {
                            $app['notification']($app['translator']->trans($e->getMessage()), 'error');
                        }
                        break;
                    default:
                        $app['notification']($app['translator']->trans("Unknown operation sent to server."), 'error');
                }
            } else {
                $app['notification']($app['translator']->trans("Some items are invalid."), 'error');
            }
        }

        return $app['twig']->render(
            'back/editorial.twig',
            [
                'title'    => $app['translator']->trans('Editorial push Edit'),
                'buttons'  => $buttons,
                'editForm' => $editForm->createView(),
            ]
        );
    }
}
