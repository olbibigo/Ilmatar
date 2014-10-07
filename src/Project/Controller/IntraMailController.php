<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;
use Ilmatar\JqGrid;
use Carbon\Carbon;

class IntraMailController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/mailbox';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::DASHBOARD,
        'type'           => \Entities\Permission::ACCESS_READ
    );
    public static $CREDENTIALS = array(
        'mailbox-send' => array (
            'functionality' => \Entities\Functionality::INTERNAL_MAIL,
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
                        ->bind('mailbox-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/', __CLASS__ . ":readStatusChangeAction")
                        ->bind('mailbox-readstatus-change'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/send', __CLASS__ . ":sendAction")
                        ->bind('mailbox-send'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app, $hasError = false)
    {
        $this->setMenu(self::$backMenus, $request, $app);

        $user = $app['security']->getToken()->getUser();

        $composeMailForm = $app['form.factory']->create(
            new \Project\Form\ComposeMailType(
                array(
                    'action' => $app['url_generator']->generate('mailbox-send'),
                )
            )
        );
        if ($hasError) {
            $composeMailForm->handleRequest($request);
            $composeMailForm->isValid();
        }
        return $app['twig']->render(
            'back/mailbox.twig',
            array(
                "title"              => $app['translator']->trans('Mailbox'),
                "receivedMessages"   => $app['orm.em']->getRepository('\Entities\InternalMail')->findBy(
                    array(
                        'to' => $user
                    ),
                    array('created_at' => 'DESC')
                ),
                "sentMessages"       => $app['orm.em']->getRepository('\Entities\InternalMail')->findBy(
                    array(
                        'from' => $user
                    ),
                    array('created_at' => 'DESC')
                ),
                'isAllowedToCompose' => $app['orm.em']->getRepository('\\Entities\\Permission')->isAllowedFunctionality(
                    $user,
                    \Entities\Functionality::INTERNAL_MAIL,
                    \Entities\Permission::ACCESS_READWRITE
                ),
                'changeReadStatusUrl'  => $app['url_generator']->generate('mailbox-readstatus-change'),
                'composeMailForm'      => $composeMailForm->createView(),
                'tabIndex'             => $hasError ? 2 : 0
            )
        );
    }

    public function readStatusChangeAction(Request $request, Application $app)
    {
        $msg = $app['orm.em']->find('\\Entities\\InternalMail', $request->get('msgId'));
        if ($msg instanceof \Entities\InternalMail) {
            $msg->setReadAt(Carbon::now());
            $app['orm.em']->persist($msg);
            $app['orm.em']->flush();
        }
        return new Response('OK');
    }

    public function sendAction(Request $request, Application $app)
    {
        $hasError = true;

        $composeMailForm = $app['form.factory']->create(
            new \Project\Form\ComposeMailType(
                array(
                    'action' => $app['url_generator']->generate('mailbox-send'),
                )
            )
        );
        $composeMailForm->handleRequest($request);
        if ($composeMailForm->isValid()) {
            $sender = $app['security']->getToken()->getUser();
            $data = $composeMailForm->getData();
            $tos = explode(',', $data['tos']);
            foreach ($tos as $to) {
                $to = trim($to);
                if (!empty($to)) {
                    $msg = new \Entities\InternalMail();
                    $msg->setSubject($data['subject']);
                    $msg->setBody($data['body']);
                    $msg->setFrom($sender);
                    $recipient = $app['orm.em']->getRepository('\\Entities\\User')->findOneByUsername($to);
                    if (($recipient instanceof \Entities\User)
                        && ($recipient->getId() != $sender->getId())) {
                        $msg->setTo($recipient);
                        try {
                            $app['orm.em']->persist($msg);
                        } catch (\Exception $e) {
                            $app['notification']($e->getMessage(), 'error');
                        }
                    } else {
                        $app['notification'](
                            $app['translator']->trans(
                                sprintf(
                                    "'%s' is not well formatted or unknown from the system or invalid in current context.",
                                    $to
                                )
                            ),
                            'warning'
                        );
                    }
                }
            }
            $app['orm.em']->flush();
            $app['notification'](
                $app['translator']->trans("Requested operation executed successfully."),
                'success'
            );
            $hasError = false;
        } else {
            $app['notification'](
                $app['translator']->trans("Some items are invalid."),
                'error'
            );
        }
        return $this->displayAction($request, $app, $hasError);
    }
}
