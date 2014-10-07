<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;
use Carbon\Carbon;

class MaintenanceController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/maintenance';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::MAINTENANCE,
        'type'           => \Entities\Permission::ACCESS_READ
    );

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
            $controllers->get('/', __CLASS__ . ":displayAction")
                        ->bind('maintenance-display'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/phpinfo', __CLASS__ . ":phpinfoAction")
                        ->bind('maintenance-phpinfo'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/check-config', __CLASS__ . ":configAction")
                        ->bind('maintenance-check-config'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/routes', __CLASS__ . ":routeAction")
                        ->bind('maintenance-routes'),
            $app
        );
        
        parent::setDefaultConfig(
            $controllers->get('/opcache', __CLASS__ . ":opcacheAction")
                        ->bind('maintenance-opcache'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/active_users', __CLASS__ . ":activeusersAction")
                        ->bind('maintenance-active-users'),
            $app
        );
        return $controllers;
    }
 
    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);

        return $app['twig']->render(
            'back/maintenance.twig',
            array(
                'title' => $app['translator']->trans('Maintenance')
            )
        );
    }

    public function phpinfoAction(Application $app)
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();
        return new Response($phpinfo);
    }
    
    public function configAction(Application $app)
    {
        $text = '<span style="font-weight:bold;color:%s">%s</span>';
       
        ob_start();
        include($app['app.root']. '/scripts/misc/config_check.php');
        $check = ob_get_contents();
        ob_end_clean();
        return new Response(
            str_replace(
                ['OK', 'KO'],
                [sprintf($text, '#0A0', 'OK'), sprintf($text, '#F00', 'KO')],
                $check
            )
        );
    }
    
    public function routeAction(Request $request, Application $app)
    {
        $routes      = $app['routes']->all();
        $repo        = $app['orm.em']->getRepository('\\Entities\\RouteAnalytics');
        $finalRoutes = [];
        foreach ($routes as $name => $route) {
            $finalRoute = array(
                'name'         => $name,
                'path'         => $route->getPath(),
                'methods'      => implode(', ', $route->getMethods()),
                'defaults'     => implode(', ', $route->getDefaults()),
                'requirements' => implode(', ', $route->getRequirements()),
                'schemes'      => implode(', ', $route->getSchemes())
            );
            $routeAnalytics = $repo->findOneByPage($name);
            if ($routeAnalytics instanceof \Entities\RouteAnalytics) {
                $finalRoute['activeAt'] = Carbon::instance($routeAnalytics->getActiveAt())->diffForHumans(Carbon::now());
                $user = $routeAnalytics->getUser();
                $finalRoute['username'] = $user->getFullname();
                $finalRoute['userid']   = $user->getId();
                $finalRoute['counter']  = $routeAnalytics->getCounter();
            } else {
                $finalRoute['counter']  = 0;
            }
            $finalRoutes[] = $finalRoute;
        }
        
        usort(
            $finalRoutes,
            function ($a, $b) {
                if ($a['counter'] == $b['counter']) {
                    return 0;
                }
                return ($a['counter'] > $b['counter']) ? -1 : 1;
            }
        );
        
        
        return $app['twig']->render(
            'back/route.twig',
            array(
                'title'  => $app['translator']->trans('Routes'),
                'routes' => $finalRoutes,
            )
        );
    }
    
    public function opcacheAction(Application $app)
    {
        $opcacheInfo = sprintf('<p class="inactive">%s</p>', $app['translator']->trans('In debug mode, opcache is disabled.'));
        if (!$app['debug']) {
            $opcacheInfo  = include($app['app.root'] . '/vendor/rlerdorf/opcache-status/opcache.php');
        }
        return new Response($opcacheInfo);
    }
    
    public function activeusersAction(Application $app)
    {
        $activeUsers = $app['orm.em']->getRepository('\\Entities\\User')->getActiveUsers(
            $app['session.lifetime'],
            $app['session.idletime']
        );

        $activeUsers = HelperFactory::build('ArrayHelper')->getHTMLTableFromArray(
            $activeUsers,
            $app['translator'],
            false,
            array('table' => 'border:1px solid black;border-collapse:collapse'),
            array(\Ilmatar\Helper\ArrayHelper::HIGHLIGHT_ZEBRA_ROW)
        );
        return new Response($activeUsers);
    }
}
