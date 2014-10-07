<?php
if (isset($_SERVER['argv'])) {
    foreach ($_SERVER['argv'] as $i => $arg) {
        if ($arg == '--test') {
            $loadTestConfig = true;
            unset($_SERVER['argv'][$i]);
            break;
        }
    }
}

if (isset($loadTestConfig) && $loadTestConfig == true) {
    $app['dbOptions'] = [
        'driver'   => $app['dbDriver-test'],
        'user'     => $app['dbUser-test'],
        'password' => $app['dbPassword-test'],
        'dbname'   => $app['dbName-test'],
        'charset'  => $app['dbCharset-test'],
        'host'     => $app['dbHost-test']
    ];
    $app['dbs.options'] = [
        'default' => $app['dbOptions'],//Default connection RW
        'r_only'  => array_merge($app['dbOptions'], ['user' => $app['dbUser'] . '_r'])//only R
    ];
    $versions = [];
    foreach (scandir($app['app.root'] . '/scripts/doctrine/schemas') as $path) {
        if (is_dir($app['app.root'] . '/scripts/doctrine/schemas/' . $path) && preg_match('/^[0-9]{14}$/', $path) === 1) {
            $versions[] = $path;
        }
    }

    sort($versions, SORT_STRING);
    $app['schemaPath'] = $app['app.root'] . '/scripts/doctrine/schemas/' . end($versions);
}

return $app;
