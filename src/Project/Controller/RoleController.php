<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Ilmatar\JqGrid;
use Project\Form\Role;
use Entities\Functionality;
use Entities\Kpi;

class RoleController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/role';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::ROLE,
        'type'           => \Entities\Permission::ACCESS_READ
    );
    public static $CREDENTIALS = array(
        'role-edit' => array (
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
                        ->bind('role-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('role-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/edit/{roleId}', __CLASS__ . ":editAction")
                        ->assert('roleId', '^-?\d*$')
                        ->value('roleId', JqGrid::ID_NEW_ENTITY)
                        ->bind('role-edit'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/users/display/{roleId}', __CLASS__ . ":usersTabAction")
                        ->assert('roleId', '^(\d*|' . JqGrid::ID_NEW_ENTITY . ')$')
                        ->bind('users-role-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/users/load/{roleId}', __CLASS__ . ":loadUsersTabAction")
                        ->assert('roleId', '^(\d*|' . JqGrid::ID_NEW_ENTITY . ')$')
                        ->bind('users-role-load'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->post('/export', __CLASS__ . ":exportAction")
                        ->bind('role-export'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Delegates to base controller
        return $this->displayJqPage(
            '\\Entities\\Role',
            $app['translator']->trans('Recorded roles'),
            array(
                'title'   => $app['translator']->trans('Role list'),
                'buttons' => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_EDIT,
                            "title"  => $app['translator']->trans('Edit selected role'),
                            "action" => array(
                                "data" => $app['url_generator']->generate('role-edit', array('roleId' => JqGrid::PARAM_URL_ROW_ID))
                            )
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_ADD,
                            "title"  => $app['translator']->trans('Add a role'),
                            "action" => array(
                                "data" => $app['url_generator']->generate('role-edit', array('roleId' => JqGrid::ID_NEW_ENTITY))
                            )
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_EXPORT,
                            "title"  => $app['translator']->trans('Export roles'),
                            "action"    => array(
                                "data" => sprintf("exportGrid('%s')", $app['url_generator']->generate('role-export'))
                            )
                        )
                    ),
                    $app
                )
            ),
            'role-load',
            $app
        );
    }

    public function usersTabAction($roleId, Request $request, Application $app)
    {
        return $app['twig']->render(
            'back/tabGrid.twig',
            array(
                'jqGridName'         => $app['translator']->trans('Users related to current role'),
                //Builds up JqGrid columns
                'jqGridColNames'     => array(
                    $app['translator']->trans('Lastname'),
                    $app['translator']->trans('Firstname'),
                ),
                'jqGridColModels'    => array(
                    array(
                        "name"          => 'lastname',
                        "searchoptions" => array(
                            "sopt"   => JqGrid::$txtOperators
                        )
                    ),
                    array(
                        "name"          => 'firsname',
                        "searchoptions" => array(
                            "sopt"   => JqGrid::$txtOperators
                        )
                    )
                ),
                'jqGridDataReadUrl'   => $app['url_generator']->generate('users-role-load', array('roleId' => $roleId)),
                'gridKey'             => 'role'
            )
        );
    }

    public function loadUsersTabAction($roleId, Request $request, Application $app)
    {
        $page     = is_null($request->get(JqGrid::JQGRID_KEY_PAGE))
            ? 1 : $request->get(JqGrid::JQGRID_KEY_PAGE);
        $pagesize = is_null($request->get(JqGrid::JQGRID_KEY_ROWS))
            ? 10 : $request->get(JqGrid::JQGRID_KEY_ROWS);

        $role        = $app['orm.em']->find('\\Entities\\Role', $roleId);
        $out         = [];
        if (JqGrid::ID_NEW_ENTITY != $roleId) {
            $out['rows'] = [];
            if (!$role->getUsers()->isEmpty()) {
                foreach ($role->getUsers() as $user) {
                    $out['rows'][] = array(
                        'lastname'  => $user->getFirstname(),
                        'firsname'  => $user->getLastname()
                    );
                }
            }

            $count        = count($out['rows']);
            $totalPages   = 0;
            if ($count > 0) {
                $totalPages = ceil($count/$pagesize);
            }
            if ($page > $totalPages) {
                $page = $totalPages;
            }

            $out['page']    = $page;
            $out['total']   = $totalPages;
            $out['records'] = $count;
        }
        return $app->json($out);
    }

    public function loadAction(Request $request, Application $app)
    {
        $repo = $app['orm.em']->getRepository('\\Entities\\Permission');
        $user = $app['security']->getToken()->getUser();

        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\Role',
                $request,
                $app,
                true,
                array(
                    '_locale'         => $request->get('_locale'),
                    'url_generator'   => $app['url_generator'],
                    'has_user_access' => $repo->isAllowedFunctionality($user, UserController::$DEFAULT_CREDENTIALS["functionality"])
                )
            )
        );
    }

    public function editAction($roleId, Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        $buttons = $this->setButtons(
            array(
                array(
                    "type"   => self::BUTTON_TYPE_LIST,
                    "title"  => $app['translator']->trans('Back to role list'),
                    "action" => array(
                        "data" => $app['url_generator']->generate('role-display')
                    )
                ),
                array(
                    "type"   => self::BUTTON_TYPE_SAVE,
                    "title"  => $app['translator']->trans('Save role')
                )
            ),
            $app
        );

        if (JqGrid::ID_NEW_ENTITY == $roleId) {
            $role = new \Entities\Role();
        } else {//EDIT or DELETE
            $role      = $app['orm.em']->find('\\Entities\\Role', $roleId);
            if (is_null($role)) {
                $app['notification']($app['translator']->trans('Requested object is unknown.', 'error'));
                return $app->redirect($app['url_generator']->generate('role-display'));
            }
            if (!$role->isAdmin()) {
                $buttons[] = $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_DELETE,
                            "title"     => $app['translator']->trans('Delete role')
                        )
                    ),
                    $app
                )[0];
            }
        }

        $functionalityCodes = array_map(
            function (Functionality $functionality) {
                return $functionality->getCode();
            },
            $app['orm.em']->getRepository('\\Entities\\Functionality')->findBy(array('is_editable' => true))
        );
        
        $kpiList = array_map(
            function (Kpi $kpi) {
                return [
                    'label'       => $kpi->getCode(),
                    'id'          => $kpi->getId(),
                    'description' => $kpi->getDescription()
                ];
            },
            $app['orm.em']->getRepository('\\Entities\\Kpi')->findBy(array('is_active' => true))
        );

        $roleForm = $app['form.factory']->create(
            new \Project\Form\RoleType(
                array(
                    'action' => $app['url_generator']->generate(
                        'role-edit',
                        array(
                            'roleId' => $roleId
                        )
                    ),
                    'permissions'        => $role->getPermissionsAsArray(),
                    'functionalityCodes' => $functionalityCodes,
                    'kpis'               => $role->getKpisAsArray(),
                    'kpiList'            => $kpiList,
                    'isAdmin'            => $role->isAdmin()
                )
            ),
            $role
        );
       
        $roleForm->handleRequest($request);

        if ($roleForm->isSubmitted()) {
            if ($roleForm->isValid()) {
                $response = $this->processChange(
                    $role,
                    $request->request->all(),
                    'role-display',
                    $app
                );
                if ($response instanceof RedirectResponse) {
                    return $response;
                }
            } else {
                $app['notification']($app['translator']->trans("Some items are invalid."), 'error');
            }
        }

        return $app['twig']->render(
            'back/role.twig',
            array(
                'title'    => $app['translator']->trans('Role Edit'),
                'buttons'  => $buttons,
                'roleForm' => $roleForm->createView(),
                'roleId'   => $roleId,
                'isAdmin'  => $role->isAdmin()
            )
        );
    }


    public function exportAction(Request $request, Application $app)
    {
         //Delegates to base controller
        return $this->exportJqPage(
            '\\Entities\\Role',
            $request,
            $app,
            array('_locale' => $request->get('_locale'))
        );
    }
}
