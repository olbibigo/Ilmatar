<?php
use Symfony\Component\HttpFoundation\Request;

$configFiles = [
    'env.xml',
    'common.xml',
    'front.xml',
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
                $app->mount($fullNamespaceClass::ROUTE_PREFIX, new $fullNamespaceClass($app));
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
                $message = 'The requested page could not be found.';
                break;
            default:
                $message = 'We are sorry, but something went terribly wrong.';
        }
        return new Response(
            $app['twig']->render(
                'front/error.twig',
                [
                    'code'    => $code,
                    'message' => $e->getMessage()
                ]
            )
        );
    }
);
/*
 * Security declaration
 */
/*
$app->register(new Silex\Provider\SecurityServiceProvider(), [
    'security.firewalls' => ...
]);
*/

if (isset($app['http_cache'])) {
    Request::setTrustedProxies(['127.0.0.1']);
    $app['http_cache']->run();
} else {
    $app->run();
}
