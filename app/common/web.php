<?php
use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\FormServiceProvider;
use Whoops\Provider\Silex\WhoopsServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Ilmatar\Provider\NotificationServiceProvider;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Neutron\ReCaptcha\ReCaptchaServiceProvider;
use Ilmatar\Provider\I18nRoutingServiceProvider;
use Gedmo\Blameable\BlameableListener;
use Symfony\Bridge\Doctrine\Form;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Ilmatar\ManagerRegistry;
use Carbon\Carbon;
use Monolog\Handler\FirePHPHandler;
use Ilmatar\Twig\Extensions\ImgBase64Extension;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Component\Translation\Translator;

/*
 * Firephp Console log
 * Brwoser plugin is required : https://addons.mozilla.org/fr/firefox/addon/firephp/
 */
if ($app['debug']) {
    $app['monolog.console'] = $app->share(function (Application $app) {
        $log = new $app['monolog.logger.class']('console');
        $log->pushHandler(new FirePHPHandler());
        return $log;
    });
}
/*
 * Route Permissions management
 */
$app->before(function (Request $request) use ($app) {
    //Gets credentials stored into the called controllers
    $controller = $request->attributes->get('_controller');
    if (!is_string($controller)) {
        return;
    }
    $class = explode(':', $controller);

    if (0 === strpos($class[0], 'web_profiler')) {
        return;
    }
    $defaultCredentials  = $class[0]::$DEFAULT_CREDENTIALS;
    $allRouteCredentials = $class[0]::$CREDENTIALS;

    $route            = $request->attributes->get('_route');
    $routeCredentials = [];
    if (array_key_exists($route, $allRouteCredentials)) {
        $routeCredentials = $allRouteCredentials[$route];
    }
    //Merges array giving priority to route credentials
    $credentials = $routeCredentials + $defaultCredentials;
    
    if (isset($app['security'])) {
        $token = $app['security']->getToken();
        //Checks ip
        //Check if IP is banished?
        if ($app["app.ip.banish.isActive"] && is_null($token) && $app['ip.isBanished']($request->getClientIp())) {
            $app->redirect('homepage');
        }        
    
        if (!is_null($credentials['functionality'])) {
            if (is_null($token)) {
                return new RedirectResponse('/');
            }
            $repo = $app['orm.em']->getRepository('\Entities\Permission');
            if (!(\Ilmatar\BaseController::isTrue($credentials, 'managed_by_action')
              || $repo->isAllowedFunctionality($token->getUser(), $credentials['functionality'], $credentials['type']))
            ) {
                return new RedirectResponse('/');
            }
        }
        $blameableListener = new BlameableListener();
        if (!is_null($token)) {
            /*
             * Doctrine behaviours
             */
            //created_by & updated_by are set in web.php
            
            $blameableListener->setUserValue($token->getUser()->getFullname());
            $app['orm.em']->getEventManager()->addEventSubscriber($blameableListener);
            
            $user  = $token->getUser();
            $now   = Carbon::now();
            $route = $request->attributes->get('_route');
            //Stores access time
            $user->setActiveAt($now);
            $app['orm.em']->persist($user);
            
            //Stores analytics
            $routeAnalytics = $app['orm.em']->getRepository('\\Entities\\RouteAnalytics')->findOneByPage($route);
            if ($routeAnalytics instanceof \Entities\RouteAnalytics) {
                $routeAnalytics->incrementCounter();
                $routeAnalytics->setUser($user);
                $routeAnalytics->setActiveAt($now);
            } else {
                $routeAnalytics = new \Entities\RouteAnalytics(
                    [
                        'page'      => $route,
                        'counter'   => 1,
                        'user'      => $user,
                        'active_at' => $now
                    ]
                );
            }
            $app['orm.em']->persist($routeAnalytics);
            
            $app['orm.em']->flush();
        } else {
            /*
             * Doctrine behaviours
             */
            //created_by & updated_by are set in web.php
            $blameableListener->setUserValue('Visitor');
            $app['orm.em']->getEventManager()->addEventSubscriber($blameableListener);
        }
    }
});
/*
 * Prevents call to internal controllers from outside
 */
$app["dispatcher"]->addListener(KernelEvents::CONTROLLER, function (FilterControllerEvent $event) use ($app) {
    $controller = $event->getRequest()->get("_controller");
    if (!is_string($controller)) {
        return;
    }
    $class = explode(':', $controller);
    $class = explode('\\', $class[0]);
    if (("Internal" == substr(array_pop($class), 0, 8)) && $event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
        $app->abort(403, 'Access forbidden from outside');
    }
});
/*
 * Templating management
 */
$app->register(
    new TwigServiceProvider(),
    [
        'twig.path'    => $app['app.root']. '/views',
        'twig.options' => [
            'charset' => "utf-8",
            'cache'   => $app['app.root'] . "/build"
        ]
    ]
);

$app['twig'] = $app->share($app->extend('twig', function (\Twig_Environment $twig, Application $app) {
    $twig->addGlobal("appName", $app['app.name'] . ' r' . $app['app.version']);
    if (isset($app['security']) && !is_null($app['security']->getToken())) {       
        $user = $app['security']->getToken()->getUser();
        $twig->addGlobal("user", $user);
        $twig->addGlobal("uiTheme", \Entities\UserSetting::getAllThemes()[$user->getSettings()[\Entities\UserSetting::THEME]]);
        $twig->addGlobal('availableUiThemes', \Entities\UserSetting::getAllThemes());    
        $twig->addGlobal(
            \Ilmatar\BaseBackController::EXPORT_FORMAT_PARAM_NAME,
            [
                \Ilmatar\BaseBackController::EXPORT_FORMAT_PDF,
                \Ilmatar\BaseBackController::EXPORT_FORMAT_XLS,
                \Ilmatar\BaseBackController::EXPORT_FORMAT_CSV,
                \Ilmatar\BaseBackController::EXPORT_FORMAT_XML
            ]
        );
        $twig->addGlobal(
            \Ilmatar\BaseBackController::EXPORT_PERIMETER_PARAM_NAME,
            [
                \Ilmatar\BaseBackController::EXPORT_PERIMETER_FULL/*,
                \Ilmatar\BaseBackController::EXPORT_PERIMETER_PAGE*/
                //@note: This comment is for hide the option "Only current page" of export perimeter. Uncomment this line for activate it
            ]
        );
        $twig->addGlobal(
            \Ilmatar\BaseBackController::EXPORT_ORIENTATION_PARAM_NAME,
            [
                \Ilmatar\Helper\PdfHelper::EXPORT_ORIENTATION_PORTRAIT,
                \Ilmatar\Helper\PdfHelper::EXPORT_ORIENTATION_PAYSAGE
            ]
        );
        $twig->addGlobal(
            \Ilmatar\BaseBackController::EXPORT_NUMBER_DATE_PARAM_NAME,
            [
                \Ilmatar\BaseBackController::EXPORT_NUMBER_DATE_ENGLISH,
                \Ilmatar\BaseBackController::EXPORT_NUMBER_DATE_LOCAL,
            ]
        );
    } else {
        $twig->addGlobal("uiTheme", \Entities\UserSetting::getAllThemes()[\Entities\UserSetting::DEFAULT_THEME]);
    }
    
    $availableLanguages = [];
    foreach($app['app.languages']['language'] as $lang) {
        $availableLanguages[$lang['code']] = $lang['name'];
    }
    $twig->addGlobal('availableLanguages', $availableLanguages);
    
    $twig->addExtension(new ImgBase64Extension($app));

    return $twig;
}));
            
$app->register(new UrlGeneratorServiceProvider());
$app->register(
    new SessionServiceProvider(),
    ['session.storage.options' => ['cookie_lifetime' => $app['session.lifetime']]]
);
$app['session']->start();
if ((time() - $app['session']->getMetadataBag()->getLastUsed()) > $app['session.idletime']) {
    $app['session']->invalidate();
}

$app->register(new ServiceControllerServiceProvider());
/*
 * Locale & Translation management
 * See also route into InternalCommonController
 */
if ($app['session']->has('locale')) {
    $app['locale']      = $app['session']->get('locale');
    $app['locale.html'] = $app['session']->get('locale.html');
    $app['locale.js']   = $app['session']->get('locale.js');
}
if ('en' != $app['locale']) {
    $app['translator'] = $app->share($app->extend('translator', function (Translator $translator, Application $app) {
        $translator->addLoader('json', new JsonFileLoader());
        $translator->addResource(
            'json',
            $app['app.var'] . '/locales/' . $app['locale'] . '.json',
            $app['locale'],
            'messages'
        );
        $translator->addResource(
            'json',
            $app['app.var'] . '/locales/' . $app['locale'] . '.json',
            $app['locale'],
            'validators'
        );
        $translator->addResource(
            'json',
            $app['app.var'] . '/locales/' . $app['locale'] . '_route.json',
            $app['locale'],
            'routes'
        );
        return $translator;
    }));    
    /*
     * Route i18n management
     */
    $app->register(new I18nRoutingServiceProvider());
}
/*
 * Notification management
 */
$app->register(new NotificationServiceProvider());
/*
 * Form management
 */
$managerRegistry = new ManagerRegistry(null, [], ['orm.em'], null, null, '\\Doctrine\\ORM\\Proxy\\Proxy');
$managerRegistry->setContainer($app);
$app->register(new ValidatorServiceProvider(),[
        "validator.validator_service_ids" => array_merge(
            isset($app['validator.validator_service_ids']) ? $app['validator.validator_service_ids'] : array(),
            ['doctrine.orm.validator.unique' => 'doctrine.orm.validator.unique_validator']
        ),
        "doctrine.orm.validator.unique_validator" => $app->share(
            function ($app) use($managerRegistry) {
                return new UniqueEntityValidator($managerRegistry);
            }
        )
    ]
);
$app->register(new FormServiceProvider());
/*
 * Allow use of the 'entity' form type in Silex
 */
$app['form.extensions'] = $app->share($app->extend('form.extensions', function ($extensions) use ($managerRegistry) {
    $extensions[] = new DoctrineOrmExtension($managerRegistry);

    return $extensions;
}));
/*
 * Captcha management
 */
if (!$app['offline']) {
    $app->register(new ReCaptchaServiceProvider(), [
        'recaptcha.public-key'  => $app['recaptcha.public-key'],
        'recaptcha.private-key' => $app['recaptcha.private-key'],
    ]);
} else {
    $app['recaptcha'] = null;
}
if ($app['debug']) {
    //Woops
    $app->register(new WhoopsServiceProvider());

    if (isset($app['twig.options'])) {
        $app['twig.options'] = array_merge($app['twig.options'], ["cache" => false]);
    }
} else {
    /*
     * caching management
     */
    $app->register(
        new HttpCacheServiceProvider(),
        [
            'http_cache.cache_dir' => $app['app.root'] . '/build',
            'http_cache.esi'       => null,
            'http_cache.options'   => [
                'allow_revalidate' => true,
                'allow_reload'     => true,
                'debug'            => $app['debug']
            ]
        ]
    );
}

return $app;
