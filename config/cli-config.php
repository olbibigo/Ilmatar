<?php
// Config for doctrine console (vendor/bin/doctrine)

use Doctrine\ORM\Tools\Console\ConsoleRunner;

$configFiles = array(
    'env.xml',
    'common.xml',
    'console.xml',
);

$app = require __DIR__ . '/../app/common/config.php';
$app = require $app['app.root'] . '/app/common/config_test.php';
$app = require $app['app.root'] . '/app/common/app.php';

return ConsoleRunner::createHelperSet($app['orm.em']);
