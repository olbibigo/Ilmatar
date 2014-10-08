<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\JqGrid;
use Project\Form\Query;

class QueryController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/query';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::QUERY,
        'type'           => \Entities\Permission::ACCESS_READ
    );
    public static $CREDENTIALS = array(
        'query-edit' => array (
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
                        ->bind('query-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('query-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/edit/{queryId}', __CLASS__ . ":editAction")
                        ->assert('queryId', '^-?\d*$')
                        ->value('queryId', JqGrid::ID_NEW_ENTITY)
                        ->bind('query-edit'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/execute/{queryId}', __CLASS__ . ":executeAction")
                        ->assert('queryId', '^(\d*|' . JqGrid::ID_NEW_ENTITY . ')$')
                        ->bind('query-execute'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/export/{queryId}', __CLASS__ . ":exportAction")
                        ->assert('queryId', '^(\d*|' . JqGrid::ID_NEW_ENTITY . ')$')
                        ->bind('query-export'),
            $app
        );
        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Delegates to base controller
        return $this->displayJqPage(
            '\\Entities\\Query',
            $app['translator']->trans('Recorded queries'),
            array(
                'title'   => $app['translator']->trans('Query list'),
                'buttons' => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_EDIT,
                            "title"  => $app['translator']->trans('Edit selected query'),
                            "action" => array(
                                "data" => $app['url_generator']->generate('query-edit', array('queryId' => JqGrid::PARAM_URL_ROW_ID))
                            )
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_ADD,
                            "title"  => $app['translator']->trans('Add a query'),
                            "action" => array(
                                "data" => $app['url_generator']->generate('query-edit', array('queryId' => JqGrid::ID_NEW_ENTITY))
                            )
                        )
                    ),
                    $app
                )
            ),
            'query-load',
            $app
        );
    }

    public function loadAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\Query',
                $request,
                $app,
                true,
                array('user' => $app['security']->getToken()->getUser())
            )
        );
    }

    public function editAction($queryId, Request $request, Application $app)
    {
        if (JqGrid::ID_NEW_ENTITY != $queryId) {
            $query = $app['orm.em']->find('\\Entities\\Query', $queryId);
            if (is_null($query)) {
                $app['notification']($app['translator']->trans('Requested object is unknown.'), 'error');
                return $app->redirect($app['url_generator']->generate('query-display'));
            }
            if (!$query->isAllowedUser($app['security']->getToken()->getUser())) {
                $app['notification']($app['translator']->trans('You have no right to access this query.'), 'error');
                return $app->redirect($app['url_generator']->generate('query-display'));
            }
        } else {
            $query = new \Entities\Query();
        }

        $this->setMenu(self::$backMenus, $request, $app);
        $buttons = $this->setButtons(
            array(
                array(
                    "type"   => self::BUTTON_TYPE_LIST,
                    "title"  => $app['translator']->trans('Back to query list'),
                    "action" => array(
                        "data" => $app['url_generator']->generate('query-display')
                    )
                ),
                array(
                    "type"   => self::BUTTON_TYPE_SAVE,
                    "title"  => $app['translator']->trans('Save query')
                )
            ),
            $app
        );
        if (JqGrid::ID_NEW_ENTITY != $queryId) {
            $buttons = array_merge(
                $buttons,
                $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_DELETE,
                            "title"     => $app['translator']->trans('Delete query')
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_EXECUTE,
                            "title"  => $app['translator']->trans('Execute recorded query'),
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_EXPORT,
                            "title"  => $app['translator']->trans('Export query results'),
                            "action"          => array(
                                "data" => sprintf("exportGrid()")
                            )
                        )
                    ),
                    $app
                )
            );
        }
        $queryForm = $app['form.factory']->create(
            new \Project\Form\QueryType(
                array(
                    'action' => $app['url_generator']->generate(
                        'query-edit',
                        array(
                            'queryId' => $queryId
                        )
                    )
                )
            ),
            $query
        );
        $queryForm->handleRequest($request);

        if ($queryForm->isSubmitted()) {
            if ($queryForm->isValid()) {
                $this->processChange(
                    $query,
                    $request->request->all(),
                    null,
                    $app
                );
                return $app->redirect(
                    $app['url_generator']->generate(
                        'query-edit',
                        array('queryId' => $query->getId())
                    )
                );
            } else {
                $app['notification']($app['translator']->trans("Some items are invalid."), 'error');
            }
        }

        return $app['twig']->render(
            'back/query.twig',
            array(
                'title'      => $app['translator']->trans('Query Edit'),
                'buttons'    => $buttons,
                'queryForm'  => $queryForm->createView(),
                'executeUrl' => $app['url_generator']->generate('query-execute', array('queryId' => $queryId)),
                'exportUrl'  => $app['url_generator']->generate('query-export', array('queryId' => $queryId))
            )
        );
    }

    public function executeAction($queryId, Request $request, Application $app)
    {
        $query = $app['orm.em']->find('\\Entities\\Query', $queryId);
        if (!$query->isAllowedUser($app['security']->getToken()->getUser())) {
            $app['notification']($app['translator']->trans('You have no right to execute this query.'), 'error');
            return $app->redirect($app['url_generator']->generate('query-display'));
        }

        try {
            $results = $app['orm.ems']['r_only']->getConnection()->fetchAll(
                $query->getQuery()
            );
        } catch (\Exception $e) {
            $results = array(array("error" => $app['translator']->trans('Invalid SQL syntax')));
        }
        return $app->json($results);
    }

    public function exportAction($queryId, Request $request, Application $app)
    {
        $query = $app['orm.em']->find('\\Entities\\Query', $queryId);
        if (!$query->isAllowedUser($app['security']->getToken()->getUser())) {
            $app['notification']($app['translator']->trans('You have no right to execute this query.'), 'error');
            return $app->redirect($app['url_generator']->generate('query-display'));
        }

        try {
            $results = $app['orm.ems']['r_only']->getConnection()->fetchAll(
                $query->getQuery()
            );
        } catch (\Exception $e) {
            $results = array(array("error" => $app['translator']->trans('Invalid SQL syntax')));
        }
        return $this->exportData($results, $request, $app);
    }
}
