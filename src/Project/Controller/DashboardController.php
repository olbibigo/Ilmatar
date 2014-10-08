<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Entities\KpiValue;

class DashboardController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin';

    const PRIVATE_DEFAULT_HOMEPAGE = 'dashboard-display';

    public static $DEFAULT_CREDENTIALS = [
        'functionality' => \Entities\Functionality::DASHBOARD,
        'type'           => \Entities\Permission::ACCESS_READ
    ];

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
            $controllers->get('dashboard', __CLASS__ . ":dashboardAction")
                        ->bind(self::PRIVATE_DEFAULT_HOMEPAGE),
            $app
        );
        
        parent::setDefaultConfig(
            $controllers->get('chart/{kpiCode}', __CLASS__ . ":loadChartAction")
                        ->bind('chart-load'),
            $app
        );
        
        parent::setDefaultConfig(
            $controllers->get('/changelog', __CLASS__ . ":changelogAction")
                        ->bind('changelog-display'),
            $app
        );
        return $controllers;
    }

    public function dashboardAction(Request $request, Application $app)
    {
        $userSettings = $app['security']->getToken()->getUser()->getSettings();
        
        //Manages locale (which is a user setting too)
        $userLocale = $userSettings[\Entities\UserSetting::LOCALE];
        if ($userLocale != $app['locale']) {
            foreach ($app['app.languages']['language'] as $lang) {
                if ($lang['code']  = $userLocale) {
                    $app['session']->set('locale', $lang['code']);
                    $app['session']->set('locale.html', $lang['code.html']);
                    $app['session']->set('locale.js', $lang['code.js']);
                    break;
                }
            }
            //redirects to load new locale
            return $app->redirect($app['url_generator']->generate(PublicBackController::PUBLIC_DEFAULT_HOMEPAGE));
        }
        
        //Display scheduled flash messages for connected users
        $app['scheduled_notification'](\Entities\FlashMessage::TARGET_ONLY_USERS);
        
        //Manages landing page (which is a user setting too)
        $landingPage = $userSettings[\Entities\UserSetting::LANDING_PAGE];
        $referer     = $request->headers->get('referer');
        if (!is_null($referer)
            //referer can be a URL into one language and login HP into another ones so following test failed
            //&& ($app['url_generator']->generate(PublicBackController::PUBLIC_LOGIN_HOMEPAGE, [], UrlGeneratorInterface::ABSOLUTE_URL) == $referer)
            //@todo: improve this
            && (0 < count(array_intersect(explode('/', $referer), ['login', 'connecter'])))
            && ($landingPage != self::PRIVATE_DEFAULT_HOMEPAGE)
        ) {
            return $app->redirect($app['url_generator']->generate($landingPage));
        }
        $this->setMenu(self::$backMenus, $request, $app);
        
        return $app['twig']->render(
            'back/dashboard.twig',
            [
                'title'              => $app['translator']->trans('Dashboard'),
                'charts'             => $this->getChartsToDisplay($app),
                'latestNews'         => $app['orm.em']->getRepository('\\Entities\\News')->getLatestNews(),
                'availableKpiViews'  => KpiValue::getAllViews()
            ]
        );
    }

    public function loadChartAction($kpiCode, Request $request, Application $app)
    {
        return $app->json(
            [
                $app['orm.em']->getRepository('\\Entities\\KpiValue')->getDataToDisplay(
                    $kpiCode,
                    $request->query->all()
                )
            ]
        );
    }
   
    public function changelogAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);

        return $app['twig']->render(
            'back/changelog.twig',
            ['title' => $app['translator']->trans('Changelog')]
        );
    }
    
    protected function getChartsToDisplay(Application $app)
    {
        $role = $app['security']->getToken()->getUser()->getRole();
        $kpis = $role->getKpis();
        
        $out = [];
        foreach ($kpis as $kpi) {
            if ($kpi->getIsActive()) {
                $out[] = [
                    'code'  => $kpi->getCode(),
                    'label' => $app['translator']->trans($kpi->getDescription())
                ];
            }
        }
        return $out;
    }
}
