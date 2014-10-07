<?php
namespace Ilmatar\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class I18nRoutingServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['route_class'] = 'Ilmatar\Route';

        $app['route_factory'] = function () use ($app) {
            $route = new $app['route_class']();
            $route->setTranslator($app['translator']);
            if (isset($app['translator.domains.route'])) {
                $route->setTranslatorDomain($app['translator.domains.route']);
            }

            return $route;
        };
    }

    public function boot(Application $app)
    {
    }
}
