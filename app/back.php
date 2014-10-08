<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Provider\SecurityServiceProvider;
use Knp\Menu\Silex\KnpMenuServiceProvider;
use Silex\Provider\RememberMeServiceProvider;
use Ilmatar\AuthenticationSuccessHandler;
use Ilmatar\AuthenticationFailureHandler;
use Ilmatar\LogoutSuccessHandler;
use Ilmatar\HelperFactory;

$configFiles = [
    'env.xml',
    'common.xml',
    'back.xml',
];

$app = require __DIR__ . '/common/config.php';
$app = require $app['app.root'] . '/app/common/app.php';
$app = require $app['app.root'] . '/app/common/web.php';

/*
 * Controllers & Routes
 */
foreach ($app['app.controllers']['value'] as $controllerName) {
    $controller = sprintf("%sController", $controllerName);
    //Try to load both the controller and its potential related internal controller declaring internal routes
    foreach ([$controller, 'Internal' . $controller] as $class) {
        foreach (['\\Project\\Controller\\', '\\Ilmatar\\'] as $namespace) {
            $fullNamespaceClass = $namespace . $class;
            if (class_exists($fullNamespaceClass)) {
                $arrayPrefix = explode("/", $fullNamespaceClass::ROUTE_PREFIX);
                foreach ($arrayPrefix as $i => $p) {
                    if (!empty($p)) {
                        $arrayPrefix[$i] = $app['translator']->trans($p, [], "routes");
                        $arrayPrefix[$i] = filter_var($arrayPrefix[$i], FILTER_SANITIZE_URL);
                    }
                }
                $prefixTrans = implode("/", $arrayPrefix);
                $app->mount($prefixTrans, new $fullNamespaceClass($app));
            }
        }
    }
}
/*
 * Error management
 */
$app->error(
    function (\Exception $e, $code) use ($app) {
        switch ($code) {
            case 404:
                $message = $app['translator']->trans('The requested page could not be found.');
                break;
            default:
                $message = $app['translator']->trans('We are sorry, but something went terribly wrong.');
        }

        return new Response(
            $app['twig']->render(
                'back/error.twig',
                [
                    'code'    => $code,
                    'message' => $message
                ]
            ),
            $code
        );
    }
);
/*
 * Security declaration
 */
$app->register(new SecurityServiceProvider());
$app->register(new Silex\Provider\RememberMeServiceProvider());
$app['security.firewalls'] = [
    'login' => [
        'pattern' => sprintf('^/%s$', $app['translator']->trans('login', [], "routes")),
    ],
    'secured' => [
        'anonymous' => false,
        'pattern' => sprintf('^/%s', $app['translator']->trans('admin', [], "routes")),
        'form' => [
            'login_path'          => sprintf('/%s', $app['translator']->trans('login', [], "routes")),
            'check_path'          => sprintf('/%s', $app['translator']->trans('admin_login_check', [], "routes")),
            'default_target_path' => sprintf('/%s/%s', $app['translator']->trans('admin', [], "routes"), $app['translator']->trans('dashboard', [], "routes"))
        ],
        'logout' => [
            'logout_path' => sprintf('/%s/logout', $app['translator']->trans('admin', [], "routes")),//No need to declare this route
            'target_url'  => "/",
        ],
        'users' => $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('\Entities\User');
        }),
        'remember_me' => [
            'key'                   => uniqid(),
            'always_remember_me'    => true,
            'remember_me_parameter' => '_remember_me',
            'lifetime'              => 31536000, //One year
        ]
    ],
];

//Authentication success handler
$app['security.authentication.success_handler.secured'] = $app->share(function ($app) {
    $handler = new AuthenticationSuccessHandler($app);
    $handler->setProviderKey('secured');
    return $handler;
});
//Logout  handler
$app['security.authentication.logout_handler.secured'] = $app->share(function ($app) {
    return new LogoutSuccessHandler(
        $app,
        isset($options['target_url']) ? $options['target_url'] : '/'
    );
});
//Authentication failure handler
if ($app["app.ip.banish.isActive"]) {
    $app['security.authentication.failure_handler.secured'] = $app->share(function ($app) {
        return new AuthenticationFailureHandler(
            $app,
            $app
        );
    });
}
//User password encoder
$app['security.encoder.digest'] = $app->share(
    function ($app) {
        return HelperFactory::build('SecurityHelper')->getEncoder();
    }
);
/*
 * Menu management
 */
$app['knp_menu.default_renderer'] = 'twig';
$app['knp_menu.template']         = 'back/menu.twig';
$app->register(new KnpMenuServiceProvider());
$app->register(new \Ilmatar\Provider\MenuServiceProvider());

/*
 * Web Profiler
 */
if ($app['debug'] && $app['profiling']) {
    $app->register(
        new \Silex\Provider\WebProfilerServiceProvider(),
        [
            'profiler.cache_dir' => $app['app.var'] . '/profiler',
            'profiler.mount_prefix' => '/_profiler'
        ]
    );
}
/*
 * IP management
 */
$app->register(new \Ilmatar\Provider\IpServiceProvider());

if (isset($app['http_cache'])) {
    Request::setTrustedProxies(array('127.0.0.1'));
    $app['http_cache']->run();
} else {
    $app->run();
}
