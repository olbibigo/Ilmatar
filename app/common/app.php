<?php
use Ilmatar\Application;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\Debug\ErrorHandler;
use Gedmo\Timestampable\TimestampableListener;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Bt51\Silex\Provider\GaufretteServiceProvider\GaufretteServiceProvider;
use Doctrine\DBAL\Types\Type;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Ilmatar\HelperFactory;
use Ilmatar\Doctrine\Listeners\AuditListener;
use Doctrine\Common\Cache\ArrayCache;

$app = new Application($app);

if ($app['debug']) {
    //Deactivates opcache
    if (extension_loaded('Zend OPcache')) {
        HelperFactory::build('PhpHelper')->setConfiguration('opcache.enable', false);
    }
} else {
    //Converts error to exception
    ErrorHandler::register();
}

/*
 * Logger management
 */
$app->register(
    new MonologServiceProvider(),
    [
        //Default logger is equivalent to the 'main' one
        'monolog.logfile' => $app['app.var'] . '/log/main.log',
        'monolog.level'   => $app['debug'] ? Logger::DEBUG :  Logger::INFO
    ]
);
if (isset($app["monolog.channels"]) && isset($app["monolog.channels"]["value"])) {
    $app['monolog.factory'] = $app->protect(function ($name) use ($app) {
        $log = new $app['monolog.logger.class']($name);
        $log->pushHandler(
            new StreamHandler(
                dirname($app['monolog.logfile']) . '/' . $name . '.log',
                $app['monolog.level']
            )
        );
        return $log;
    });

    if (is_array($app["monolog.channels"]["value"])) {
        foreach ($app["monolog.channels"]["value"] as $channel) {
            $app['monolog.' . $channel] = $app->share(function ($app) use ($channel) {
                return $app['monolog.factory']($channel);
            });
        }
    } else {
        $channel = $app["monolog.channels"]["value"];
        $app['monolog.' . $channel] = $app->share(function ($app) use ($channel) {
            return $app['monolog.factory']($channel);
        });
    }
}
/*
 * File System abstraction
 */
$app->register(
    new GaufretteServiceProvider(),
    [
        'gaufrette.adapter.class' => 'Local',
        'gaufrette.options' => [$app['app.var']]
    ]
);
/*
 * Mailer
 */
$app->register(
    new SwiftmailerServiceProvider(),
    ['swiftmailer.options' => $app['swiftmailer-options']]
);
/*
 * ORM and behaviors
 */
$app->register(new DoctrineServiceProvider);//use $app['dbs.options']

$app->register(
    new DoctrineOrmServiceProvider,
    [
        'orm.proxies_dir'           => $app['app.root'] . '/scripts/doctrine/proxies',
        'orm.auto_generate_proxies' => false,
        'orm.proxies_namespace'     => 'DoctrineProxies',
        'orm.ems.options'           => [
            //Default entity manager (RW accesss)
            'default'  => [
                'connection' => 'default',
                'mappings'   => [
                    [
                        'type'      => 'yml',
                        'namespace' => 'Entities',
                        'path'      => $app['schemaPath']
                    ]
                ]
            ],
            //Restricted entity manager (R only). Used by the Query manager
            'r_only'  => [
                'connection' => 'r_only',
                'mappings'   => [
                    [
                        'type'      => 'yml',
                        'namespace' => 'Entities',
                        'path'      => $app['schemaPath']
                    ]
                ]
            ]
        ]
    ]
);
$ormEvMa = $app['orm.em']->getEventManager();
$ormCoMa = $app['orm.em']->getConfiguration();
//DOCTRINE behaviors
//History
$ormEvMa->addEventSubscriber(new AuditListener());
//created_at & updated_at
$ormEvMa->addEventSubscriber(new TimestampableListener());
//deleted_at
$ormEvMa->addEventSubscriber(new SoftDeleteableListener());
$ormCoMa->addFilter('soft-deleteable', '\\Gedmo\\SoftDeleteable\\Filter\\SoftDeleteableFilter');
$app['orm.em']->getFilters()->enable('soft-deleteable');
//DOCTRINE extensions
$ormCoMa->addCustomNumericFunction('TIMESTAMPDIFF', 'Ilmatar\Doctrine\Extensions\MySqlTimestampDiff');
$ormCoMa->addCustomNumericFunction('DATE', 'Ilmatar\Doctrine\Extensions\MySqlDate');
$ormCoMa->addCustomNumericFunction('WEEK', 'Ilmatar\Doctrine\Extensions\MySqlWeek');
$ormCoMa->addCustomNumericFunction('MONTH', 'Ilmatar\Doctrine\Extensions\MySqlMonth');
$ormCoMa->addCustomNumericFunction('YEAR', 'Ilmatar\Doctrine\Extensions\MySqlYear');
$ormCoMa->addCustomNumericFunction('DATE_FORMAT', 'Ilmatar\Doctrine\Extensions\MySqlDateFormat');
if (!Type::hasType('encryptedstring')) {
    Type::addType('encryptedstring', '\Ilmatar\Doctrine\Extensions\EncryptedStringType');
    $app['orm.em']->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('encryptedstring', 'encryptedstring');
}
//DOCTRINE cache
//@todo: change in production (see http://docs.doctrine-project.org/en/2.0.x/reference/caching.html)
$ormCoMa->setQueryCacheImpl(new ArrayCache());
$ormCoMa->setQueryCacheImpl(new ArrayCache());
$ormCoMa->setMetadataCacheImpl(new ArrayCache());
/*
 * Locale & Translation management
 * See also route into InternalCommonController
 */
$app->register(new TranslationServiceProvider());
$lang               = $app['app.languages']['language'][0];
$app['locale']      = $lang['code'];
$app['locale.html'] = $lang['code.html'];
$app['locale.js']   = $lang['code.js'];

$app['locale_fallback'] = [$app['locale']];

if ('en' != $app['locale']) {
    $app['translator'] = $app->share($app->extend('translator', function ($translator, $app) {
        $translator->addLoader('json', new JsonFileLoader());
        $translator->addResource(
            'json',
            $app['app.var'] . '/locales/' . $app['locale'] . '.json',
            $app['locale'],
            'messages'
        );
        return $translator;
    }));
}

return $app;
