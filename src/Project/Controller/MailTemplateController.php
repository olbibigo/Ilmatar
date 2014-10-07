<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;
use Ilmatar\JqGrid;
use Project\Form\Mail;

class MailTemplateController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/mail-template';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::MAIL_TEMPLATE,
        'type'           => \Entities\Permission::ACCESS_READ
    );
    public static $CREDENTIALS = array(
        'mail-template-edit' => array (
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
                        ->bind('mail-template-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('mail-template-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/edit/{mailId}', __CLASS__ . ":editAction")
                        ->assert('mailId', '^-?\d*$')
                        ->value('mailId', JqGrid::ID_NEW_ENTITY)
                        ->bind('mail-template-edit'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Delegates to base controller
        return $this->displayJqPage(
            '\\Entities\\MailTemplate',
            $app['translator']->trans('Recorded mail templates'),
            array(
                'title'   => $app['translator']->trans('Mail template list'),
                'buttons' => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_EDIT,
                            "title"  => $app['translator']->trans('Edit selected mail template'),
                            "action" => array(
                                "data" => $app['url_generator']->generate('mail-template-edit', array('mailId' => JqGrid::PARAM_URL_ROW_ID))
                            )
                        )
                    ),
                    $app
                )
            ),
            'mail-template-load',
            $app
        );
    }

    public function loadAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\MailTemplate',
                $request,
                $app
            )
        );
    }

    public function editAction($mailId, Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);

        $buttons = $this->setButtons(
            array(
                array(
                    "type"   => self::BUTTON_TYPE_LIST,
                    "title"  => $app['translator']->trans('Back to mail template list'),
                    "action" => array(
                        "data" => $app['url_generator']->generate('mail-template-display')
                    )
                ),
                array(
                    "type"   => self::BUTTON_TYPE_SAVE,
                    "title"  => $app['translator']->trans('Save mail template'),
                )
            ),
            $app
        );
        
        $mail = $app['orm.em']->find('\\Entities\\MailTemplate', $mailId);
        if (!$mail instanceof \Entities\MailTemplate) {
            $app->abort(
                403,
                sprintf(
                    $app['translator']->trans("Object %s does not exist."),
                    $mailId
                )
            );
        }

        $mailForm = $app['form.factory']->create(
            new \Project\Form\mailTemplateType(
                array(
                    'action' => $app['url_generator']->generate(
                        'mail-template-edit',
                        array(
                            'mailId' => $mailId
                        )
                    )
                )
            ),
            $mail
        );
        $mailForm->handleRequest($request);

        if ($mailForm->isSubmitted()) {
            if ($mailForm->isValid()) {
                $oper = $request->get(JqGrid::JQGRID_KEY_OPER);
                switch($oper) {
                    case JqGrid::JQGRID_ACTION_UPDATE:
                        try {
                            $app['orm.em']->persist($mail);
                            $app['orm.em']->flush();
                            $app['notification'](
                                sprintf(
                                    $app['translator']->trans("Requested operation executed successfully (id: %s)."),
                                    $mail->getId()
                                ),
                                'success'
                            );
                            //Redirects to list
                            return $app->redirect($app['url_generator']->generate('mail-template-display'));
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
            'back/mailTemplate.twig',
            array(
                'title'    => $app['translator']->trans('Mail template Edit'),
                'buttons'  => $buttons,
                'mailForm' => $mailForm->createView(),
            )
        );
    }
}
