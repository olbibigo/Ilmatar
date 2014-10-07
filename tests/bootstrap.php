<?php
$configFiles = array(
    'env.xml',
    'common.xml',
    'console.xml',
);

$app = require __DIR__ . '/../app/common/config.php';
$autoloader->add('Tests', __DIR__);

$loadTestConfig = true;
$app = require $app['app.root'] . '/app/common/config_test.php';

$app = require $app['app.root'] . '/app/common/app.php';
$app = require $app['app.root'] . '/app/common/web.php';
$app = require $app['app.root'] . '/app/common/console.php';

//Fake routes for testing
foreach (array('fake-route', 'pipo-user-select', 'pipo-functionality-select') as $name) {
    $app->get('/' . $name, function () {
        return new \Symfony\Component\HttpFoundation\Response('');
    })->bind($name);
}
//Write logs into special files
$app['monolog.factory'] = $app->protect(function ($name) use ($app) {
    $log = new $app['monolog.logger.class']($name);
    $log->pushHandler(
        new \Monolog\Handler\StreamHandler(
            dirname($app['monolog.logfile']) . '/test.' . $name . '.log',
            Monolog\Logger::DEBUG
        )
    );
    return $log;
});
//Add jobby commands
//Loads all declared commands from console.xml
if (isset($app["command.classes"]) && isset($app["command.classes"]["value"])) {
    $namespaces = [
       "\\Project\\Command\\Jobby\\",
       "\\Ilmatar\\Command\\Jobby\\",
    ];
    //Loads all active commands from the database
    $jobs = $app["orm.em"]->getRepository('\\Entities\\Job')->getJobsReady();
    foreach ($jobs as $job) {
        foreach ($namespaces as $namespace) {
            $className = $namespace . $job->getClass();
            if (class_exists($className)) {
                $app['console']->add(new $className());
                break;
            }
        }
    }
}
if (isset($app['console'])) {
    $app['console']->setAutoExit(false);
    $app['console']->setCatchExceptions(false);
}
