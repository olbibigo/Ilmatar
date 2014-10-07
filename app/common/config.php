<?php
use Symfony\Component\Config\Util\XmlUtils;

$app = ['app.root' => dirname(dirname(__DIR__))];

if (! isset($autoloader)) {
    $autoloader = require $app['app.root'] . '/vendor/autoload.php';
}

if (isset($configFiles)) {
    foreach ($configFiles as $file) {
        $app = array_merge($app, XmlUtils::convertDomElementToArray(XmlUtils::loadFile($app['app.root'] . '/config/' . $file)->firstChild));
    }
}
/*
 * DB connection configuration
 */
$app['dbOptions'] = [
    'driver'   => $app['dbDriver'],
    'password' => $app['dbPassword'],
    'dbname'   => $app['dbName'],
    'charset'  => $app['dbCharset'],
    'user'     => $app['dbUser'],
    'host'     => $app['dbHost']
];
$app['dbs.options'] = [
    'default' => $app['dbOptions'],//Default connection RW
    'r_only'  => array_merge($app['dbOptions'], ['user' => $app['dbUser'] . '_r'])//only R
];

$app['schemaPath'] = $app['app.root'] . '/scripts/doctrine/schemas/current';

return $app;
