<?php
namespace Project\Controller;

use Ilmatar\Application;
use Ilmatar\TagManager;
use Ilmatar\HelperFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\Helper;
use Zend\Text\Figlet\Figlet;

class PublicBackController extends BaseBackController
{
    const ROUTE_PREFIX = '';
    
    const FILE_TYPE_REPORT = 'report';
    
    const PUBLIC_DEFAULT_HOMEPAGE = 'index';
    const PUBLIC_LOGIN_HOMEPAGE   = 'homepage';
    
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
            $controllers->get('/', __CLASS__ . ":indexAction")
                        ->bind(self::PUBLIC_DEFAULT_HOMEPAGE),
            $app
        );
        parent::setDefaultConfig(
            $controllers->get('/login', __CLASS__ . ":loginAction")
                        ->bind(self::PUBLIC_LOGIN_HOMEPAGE),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/reset_password/', __CLASS__ . ":resetPasswordAction")
                        ->bind('reset_password'),
            $app
        );
        
        parent::setDefaultConfig(
            $controllers->get('/probe', __CLASS__ . ":probeAction")
                        ->bind('probe'),
            $app
        );
        
        //Warning : this route is used by SendReportCommand using its pattern
        parent::setDefaultConfig(
            $controllers->get('/report-download/{type}/{token}', __CLASS__ . ":reportDownloadAction")
                        ->convert('token', function ($token) {
                            return HelperFactory::build('SecurityHelper')->decryptString(rawurldecode($token));
                        })
                        ->bind('report-public-download'),
            $app
        );
        
        //Easter egg because all good apps should have one :)
        parent::setDefaultConfig(
            $controllers->get('/figlet/{text}', __CLASS__ . ":figletAction")
                        ->value('text', 'Ilmatar rocks your world!')
                        ->bind('figlet'),
            $app
        );
        return $controllers;
    }

    public function indexAction(Request $request, Application $app)
    {
        //Set new locale if any
        $lg = $request->get("lg");
        if (is_null($lg)) {
            return $app->redirect($app['url_generator']->generate(DashboardController::PRIVATE_DEFAULT_HOMEPAGE));
        }
        foreach ($app['app.languages']['language'] as $lang) {
            if ($lang['code'] = $lg) {
                $app['session']->set('locale', $lg);
                $app['session']->set('locale.html', $lang['code.html']);
                $app['session']->set('locale.js', $lang['code.js']);
                break;
            }
        }
        return $app->redirect($app['url_generator']->generate(self::PUBLIC_DEFAULT_HOMEPAGE));
    }
  
    public function loginAction(Request $request, Application $app)
    {
        //Check if IP is banished?
        $isBanished = $app['ip.isBanished']($request->getClientIp(), true);
        
        //Display scheduled flash messages for all visitors
        $referer = $request->headers->get('referer');
        if (is_null($referer)
            || (false === strpos($referer, '/admin'))) {
            $app['scheduled_notification'](\Entities\FlashMessage::TARGET_ALL);
        }

        return $app['twig']->render(
            'back/homepage.twig',
            [
                'title'               => $app['translator']->trans('Back office homepage'),
                'styles'              => ['/assets/homepage.css'],
                'error'               => $app['security.last_error']($request),
                'last_username'       => $app['session']->get('_security.last_username'),
                'recaptcha'           => $app['recaptcha'], //new captcha
                'forgottenActionUrl'  => $app['url_generator']->generate('reset_password'),
                'isBanished'          => $isBanished,
                'metas'               => [
                                            [
                                                "name"    => "description",
                                                "content" => $app['translator']->trans("Back office homepage")
                                            ]
                                         ]
            ]
        );
    }

    public function resetPasswordAction(Request $request, Application $app)
    {
        //Check captcha validity
        if (!is_null($app['recaptcha'])) {
            $response  = $app['recaptcha']->bind($request);
        }
        if ((isset($response) && $response->isValid())
           || !isset($response)) {
            $user = $app['orm.em']->getRepository('\\Entities\\User')->findOneByUsername($request->request->get('email'));
            if (!is_null($user)) {
                $securityHelper = HelperFactory::build(
                    'SecurityHelper',
                    [],
                    [
                        'seedFile'                 => $app['app.var'] . '/seedFile.txt',
                        'security.encoder_factory' => $app['security.encoder_factory']
                    ]
                );
                //Reset Password
                $password = $securityHelper->generatePassword(\Entities\User::MIN_PASSWORD_LENGTH);
                $user->setPassword(
                    $securityHelper->encodePasswordForUser($user, $password)
                );
                $app['orm.em']->persist($user);
                $app['orm.em']->flush();

                $mailHelper = HelperFactory::build(
                    'MailHelper',
                    [
                        'mailer'             => $app['mailer'],
                        'templateRepository' => $app['orm.em']->getRepository('\\Entities\\MailTemplate')
                    ],
                    ['logger' => $app['monolog.mailer']]
                );

                $mailHelper->createAndSendMessageFromTemplate(
                    \Entities\MailTemplate::RENEW_PASSWORD,
                    new TagManager(
                        $app['app.tags.strategies'],
                        [
                            'user'     => $user,
                            'password' => $password
                        ]
                    ),
                    [$app['app.mail.sender']],
                    [$user->getUsername()]
                );
            }
            //Returns willingly a OK message even if the user do not exist
            return $app->json(
                ["error" => false, "message" => $app['translator']->trans("Your new password has been sent.")]
            );
        } else {
            return $app->json(
                [
                    "error"   => true,
                    "message" => sprintf(
                        $app['translator']->trans(
                            sprintf(
                                "Captcha check failed (%s).",
                                $response->getError()
                            )
                        )
                    )
                ]
            );
        }
    }
    
    public function probeAction(Application $app)
    {
        $count = count($app['orm.em']->getRepository('\\Entities\\User')->findAll());
        return new Response($count > 0 ? 'OK' : 'KO');
    }
    
    public function reportDownloadAction($type, $token, Application $app)
    {
        return self::download(
            $token,
            $type,
            [self::FILE_TYPE_REPORT => $app['app.var']. '/export/'],
            $app
        );
    }
    
    public function figletAction($text, Application $app)
    {
        return new Response(
            sprintf(
                '<html><head></head><body><pre>%s</pre></body>',
                (new Figlet(/*['font' => '....flf']*/))->render($text)
            )
        );
    }
}
