<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Ilmatar\HelperFactory;
use Ilmatar\JqGrid;
use Project\Form\User;
use Symfony\Component\Validator\Constraints as Assert;
use Ilmatar\Helper\FileSystemHelper;

class UserController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/user';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::USER,
        'type'           => \Entities\Permission::ACCESS_READ
    );
    public static $CREDENTIALS = array(
        'user-edit' => array (
            'type'              => \Entities\Permission::ACCESS_READWRITE,
            'managed_by_action' => true
        ),
        'user-edit-settings' => array (
            'functionality' => \Entities\Functionality::DASHBOARD,
            'type'           => \Entities\Permission::ACCESS_READ
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
                        ->bind('user-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('user-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/edit/{userId}', __CLASS__ . ":editAction")
                        ->assert('userId', '^-?\d*$')
                        ->value('userId', JqGrid::ID_NEW_ENTITY)
                        ->bind('user-edit'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/export', __CLASS__ . ":exportAction")
                        ->bind('user-export'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->post('/autocomplete', __CLASS__ . ":autocompleteAction")
                        ->bind('user-autocomplete'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->post('/edit_settings', __CLASS__ . ":editSettingsAction")
                        ->bind('user-edit-settings'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);

        return $this->displayJqPage(
            '\\Entities\\User',
            $app['translator']->trans('Recorded users'),
            array(
                'title'   => $app['translator']->trans('User list'),
                'buttons' => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_EDIT,
                            "title"  => $app['translator']->trans('Edit selected user'),
                            "action" => array(
                                "data" => $app['url_generator']->generate('user-edit', array('userId' => JqGrid::PARAM_URL_ROW_ID))
                            )
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_ADD,
                            "title"  => $app['translator']->trans('Add a user'),
                            "action" => array(
                                "data" => $app['url_generator']->generate('user-edit', array('userId' => JqGrid::ID_NEW_ENTITY))
                            )
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_EXPORT,
                            "title"  => $app['translator']->trans('Export users'),
                            "action"    => array(
                                "data" => sprintf("exportGrid('%s')", $app['url_generator']->generate('user-export'))
                            )
                        )
                    ),
                    $app
                ),
            ),
            'user-load',
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
                '\\Entities\\User',
                $request,
                $app,
                true,
                array(
                    '_locale'         => $request->get('_locale'),
                    'url_generator'   => $app['url_generator'],
                    'has_role_access' => $repo->isAllowedFunctionality($user, RoleController::$DEFAULT_CREDENTIALS["functionality"])
                )
            )
        );
    }

    public function editAction($userId, Request $request, Application $app)
    {
        $currentUser             = $app['security']->getToken()->getUser();
        $permissionRepo          = $app['orm.em']->getRepository('\Entities\Permission');
        $isAllowedFunctionality = $permissionRepo->isAllowedFunctionality($currentUser, self::$DEFAULT_CREDENTIALS['functionality'], self::$CREDENTIALS['user-edit']['type']);

        if (!(($userId == $currentUser->getId()) || $isAllowedFunctionality)) {
            return $app->redirect('/');
        }
        $this->setMenu(self::$backMenus, $request, $app);

        $buttons = $this->setButtons(
            array(
                array(
                    "type"   => self::BUTTON_TYPE_LIST,
                    "title"  => $app['translator']->trans('Back to user list'),
                    "id"     => "back",
                    "action" => array(
                        "data" => $app['url_generator']->generate('user-display')
                    )
                ),
                array(
                    "type"   => self::BUTTON_TYPE_SAVE,
                    "title"  => $app['translator']->trans('Save user'),
                    "id"     => "save"
                ),
                array(
                    "type"   => self::BUTTON_TYPE_ADD,
                    "title"  => $app['translator']->trans('Upload documents'),
                    "id"     => "upload",
                    "action" => array(
                        "type" => self::BUTTON_ACTION_OPEN_MODAL_WINDOW,
                        "data" => "uploadFormContainer"
                    )
                )
            ),
            $app,
            false
        );
        $oper   = $request->get(JqGrid::JQGRID_KEY_OPER);
        $byPass = false;
        if (JqGrid::ID_NEW_ENTITY == $userId) {
            $user = new \Entities\User();
        } else {//EDIT or DELETE
            $user      = $app['orm.em']->find('\\Entities\\User', $userId);
            if (is_null($user)) {
                $app['notification']($app['translator']->trans('Requested object is unknown.', 'error'));
                return $app->redirect($app['url_generator']->generate('user-display'));
            }
            $adminRole = $app['orm.em']->getRepository('\\Entities\\Role')->findOneByCode(\Entities\Role::TECHNICAL_ADMIN_CODE);
            $newUser   = $request->get('user');

            if (!is_null($newUser)) {
                //At least one technical ADMIN user must be kept
                if ($user->isAdmin()
                    && ((isset($newUser['role']) && ($newUser['role'] != $adminRole->getId()))
                        ||($oper == JqGrid::JQGRID_ACTION_DELETE))) {
                    if ($app['orm.em']->getRepository('\\Entities\\User')->isSingle(['role' => $adminRole])) {
                        $byPass = true;
                        $app['notification']($app['translator']->trans("At least one technical administrator account must remain."), 'error');
                    }
                }
            }
            if ($oper == JqGrid::JQGRID_ACTION_DELETE) {
                //User cannot delete his own account
                if ($user->getId() == $app['security']->getToken()->getUser()->getId()) {
                    $byPass = true;
                    $app['notification']($app['translator']->trans("You cannot delete your own user account."), 'error');
                }
            
            }
            $originalPassword = $user->getPassword();

            //User cannot delete his own account
            if ($user->getId() != $app['security']->getToken()->getUser()->getId()) {
                $buttons[] = $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_DELETE,
                            "title"     => $app['translator']->trans('Delete user'),
                            "id"     => "delete",
                        )
                    ),
                    $app
                )[0];
            }
        }
        $userForm = $app['form.factory']->create(
            new \Project\Form\UserType(
                array(
                    'locale'      => $request->get('_locale'),
                    'action'      => $app['url_generator']->generate(
                        'user-edit',
                        ['userId' => $userId]
                    ),
                    'userId'      => $userId,
                    'settingList' => \Entities\UserSetting::getDefaultSettings(true),
                    'settings'    => $user->getSettings(),
                    'isLdapUser'  => self::isTrue($app['security.firewalls']['secured'], 'ldap')
                )
            ),
            $user
        );
       
        if (!$byPass) {
            $userForm->handleRequest($request);

            if ($userForm->isSubmitted()) {
                if ($userForm->isValid()) {
                    $password = $userForm->get('password')->getData();
                    if (!empty($password)) {
                        $securityHelper = HelperFactory::build(
                            'SecurityHelper',
                            [],
                            ['security.encoder_factory' => $app['security.encoder_factory']]
                        );
                        $user->setPassword(
                            $securityHelper->encodePasswordForUser($user, $password)
                        );
                    } elseif (isset($originalPassword)) {
                        $user->setPassword($originalPassword);
                    }
                    
                    $response = $this->processChange(
                        $user,
                        $request->request->all(),
                        'user-display',
                        $app
                    );
                    if ($response instanceof RedirectResponse) {
                        return $response;
                    }
                } else {
                    $app['notification']($app['translator']->trans("Some items are invalid."), 'error');
                }
            }
        }

        $tabbutton = array(
            'save',
            'delete'
        );
        return $app['twig']->render(
            'back/user.twig',
            array(
                'title'             => $app['translator']->trans('User Edit'),
                'buttons'           => $buttons,
                'buttonByTab'       =>
                array(
                    'persontab' => $tabbutton,
                    'logintab' => $tabbutton,
                    'contacttab' => $tabbutton,
                    'settingstab' => $tabbutton,
                    'documenttab' =>
                        array(
                            'upload'
                        )
                ),
                'userForm'                  => $userForm->createView(),
                'isAllowedToModifyDocument' => $isAllowedFunctionality,
                'loginAt'                   => is_null($user->getLoginAt()) ? $app['translator']->trans("N/A") : $user->getLoginAt()->format(Jqgrid::DATETIME_DISPLAY_FORMAT),
                'activeAt'                  => is_null($user->getActiveAt()) ? $app['translator']->trans("N/A") : $user->getActiveAt()->format(Jqgrid::DATETIME_DISPLAY_FORMAT),
                'logoutAt'                  => is_null($user->getLogoutAt()) ? $app['translator']->trans("N/A") : $user->getLogoutAt()->format(Jqgrid::DATETIME_DISPLAY_FORMAT),
                'userId'                    => $userId
            )
        );
    }


    public function exportAction(Request $request, Application $app)
    {
         //Delegates to base controller
        return $this->exportJqPage(
            '\\Entities\\User',
            $request,
            $app,
            array('_locale' => $request->get('_locale'))
        );
    }
    
    public function autocompleteAction(Request $request, Application $app)
    {
        $term   = $request->get('term');
        $qb     = $app['orm.em']->createQueryBuilder('o');
        $users  = $qb
            ->select('u.firstname, u.lastname, u.username')
            ->from('\Entities\User', 'u')
            ->where($qb->expr()->like('u.username', ':username'))
            ->orderBy('u.username', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(10)
            ->setParameter('username', '%' . $term . '%')
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        //returns a simple array
        return $app->json(
            array_map(
                function ($item) {
                    return sprintf(
                        '%s',
                        $item['username']
                    );
                },
                $users
            )
        );
    }
    
    public function editSettingsAction(Request $request, Application $app)
    {
        if (is_null($app['security']->getToken())) {
            return $app->json(array("error" => true, "message" => "Invalid token"));
        }
        $user = $app['security']->getToken()->getUser();
        
        $app['orm.em']->getRepository('\\Entities\\User')->setUserSettings(
            $user,
            $request->request->all(),
            true
        );
        return $app->json(array("error" => false, "message" => "User settings have been saved."));
    }
}
