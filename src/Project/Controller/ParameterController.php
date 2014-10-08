<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Ilmatar\JqGrid;

class ParameterController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/parameter';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::GLOBAL_PARAMETER,
        'type'           => \Entities\Permission::ACCESS_READ
    );
    public static $CREDENTIALS = array(
        'parameter-edit' => array (
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
                        ->bind('parameter-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('parameter-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/edit/{parameterId}', __CLASS__ . ":editAction")
                        ->assert('parameterId', '^-?\d*$')
                        ->bind('parameter-edit'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/export', __CLASS__ . ":exportAction")
                        ->bind('parameter-export'),
            $app
        );
        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Delegates to base controller
        $options = array('title' => $app['translator']->trans('Parameter list'));

        if ($app['orm.em']->getRepository('\\Entities\\Parameter')->findOneBy(array('is_readonly' => false))) {
            $options['buttons'] = $this->setButtons(
                [
                    [
                        "type"   => self::BUTTON_TYPE_EDIT,
                        "title"  => $app['translator']->trans('Edit selected parameter'),
                        "action" => array(
                            "data" => $app['url_generator']->generate('parameter-edit', array('parameterId' => JqGrid::PARAM_URL_ROW_ID))
                        )
                    ],
                    [
                        "type"   => self::BUTTON_TYPE_EXPORT,
                        "title"  => $app['translator']->trans('Export parameters'),
                        "action"    => array(
                            "data" => sprintf("exportGrid('%s')", $app['url_generator']->generate('parameter-export'))
                        )
                    ]
                ],
                $app
            );
        }
        return $this->displayJqPage(
            '\\Entities\\Parameter',
            $app['translator']->trans('Recorded parameters'),
            $options,
            'parameter-load',
            $app
        );
    }
    
    public function loadAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\Parameter',
                $request,
                $app
            )
        );
    }
    
    public function editAction($parameterId, Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);

        $buttons = $this->setButtons(
            [
                [
                    "type"   => self::BUTTON_TYPE_LIST,
                    "title"  => $app['translator']->trans('Back to parameter list'),
                    "action" => [
                        "data" => $app['url_generator']->generate('parameter-display')
                    ]
                ],
                [
                    "type"   => self::BUTTON_TYPE_SAVE,
                    "title"  => $app['translator']->trans('Save parameter'),
                ]
            ],
            $app
        );
        
        $parameter = $app['orm.em']->find('\\Entities\\Parameter', $parameterId);
        if (!$parameter instanceof \Entities\Parameter) {
            $app->abort(
                403,
                sprintf(
                    $app['translator']->trans("Object %s does not exist."),
                    $parameterId
                )
            );
        }

        $parameterForm = $app['form.factory']->create(
            new \Project\Form\ParameterType(
                [
                    'action' => $app['url_generator']->generate(
                        'parameter-edit',
                        [
                            'parameterId' => $parameterId
                        ]
                    ),
                    'isReadOnly' => $parameter->getIsReadonly(),
                    'type'       => $parameter->getType(),
                    'code'       => $parameter->getCode()
                ]
            ),
            $parameter
        );
        $parameterForm->handleRequest($request);

        if ($parameterForm->isSubmitted()) {
            if ($parameterForm->isValid()) {
                $oper = $request->get(JqGrid::JQGRID_KEY_OPER);
                switch($oper) {
                    case JqGrid::JQGRID_ACTION_UPDATE:
                        try {
                            $app['orm.em']->persist($parameter);
                            $app['orm.em']->flush();
                            $app['notification'](
                                sprintf(
                                    $app['translator']->trans("Requested operation executed successfully (id: %s)."),
                                    $parameter->getId()
                                ),
                                'success'
                            );
                            //Redirects to list
                            return $app->redirect($app['url_generator']->generate('parameter-display'));
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
            'back/parameter.twig',
            [
                'title'         => $app['translator']->trans('Parameter Edit'),
                'buttons'       => $buttons,
                'parameterForm' => $parameterForm->createView(),
            ]
        );
    }
    
    public function exportAction(Request $request, Application $app)
    {
         //Delegates to base controller
        return $this->exportJqPage(
            '\\Entities\\Parameter',
            $request,
            $app,
            array('_locale' => $request->get('_locale'))
        );
    }
}
