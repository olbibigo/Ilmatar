<?php
namespace Ilmatar\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Carbon\Carbon;
use Ilmatar\Provider\NotificationServiceProvider;

class IpServiceProvider implements ServiceProviderInterface
{
    const NB_LOGIN_ATTEMPT = 'nb_login_attempt';

    public function register(Application $app)
    {
        $app['ip.isBanished'] = $app->protect(function ($ip, $isNotified = false) use ($app) {
            $ipBlacklist = $app['orm.em']->getrepository('\\Entities\\IpBlacklist')->findOneByIp($ip);
            if ($ipBlacklist instanceof \Entities\IpBlacklist) {
                $now = Carbon::now();
                if ($ipBlacklist->getUntilDate() >= $now) {
                    if ($isNotified) {
                        $app['notification'](
                            sprintf(
                                $app['translator']->trans('Due to too many login attempts, your computer is banished for the next %s seconds.'),
                                Carbon::instance($ipBlacklist->getUntilDate())->diffInSeconds($now)
                            ),
                            'error',
                            NotificationServiceProvider::RESET_MODE_ALL
                        );
                    }
                    return true;
                } else {
                    $app['ip.removeFromBlacklist']($ip);
                }
            }
            return false;
        });

        $app['ip.removeFromBlacklist'] = $app->protect(function ($ip) use ($app) {
            $app['session']->remove(self::NB_LOGIN_ATTEMPT);
            $ipBlacklist = $app['orm.em']->getRepository('\\Entities\\IpBlacklist')->findOneByIp($ip);
            if ($ipBlacklist instanceof \Entities\IpBlacklist) {
                $app['orm.em']->remove($ipBlacklist);
                $app['orm.em']->flush();
            }
        });

        $app['ip.manageLoginAttempt'] = $app->protect(function ($ip) use ($app) {
            if ($app['session']->has(self::NB_LOGIN_ATTEMPT)) {
                $app['session']->set(
                    self::NB_LOGIN_ATTEMPT,
                    $app['session']->get(self::NB_LOGIN_ATTEMPT) + 1
                );
                //Appends to IP blacklist

                if ($app['session']->get(self::NB_LOGIN_ATTEMPT) >= $app['app.max.login.attempt']) {
                    $date = Carbon::now()->addMinutes($app['app.ip.banish.delay']);
                    $ipBlacklist = $app['orm.em']->getRepository('\\Entities\\IpBlacklist')->findOneByIp($ip);
                    if ($ipBlacklist instanceof \Entities\IpBlacklist) {
                        $ipBlacklist->setUntilDate($date);
                    } else {
                        $ipBlacklist = new \Entities\IpBlacklist(
                            [
                                'ip'         => $ip,
                                'until_date' => $date
                            ]
                        );
                    }
                    $app['orm.em']->persist($ipBlacklist);
                    $app['orm.em']->flush();
                }
            } else {
                $app['session']->set(self::NB_LOGIN_ATTEMPT, 1);
            }
        });
    }

    public function boot(Application $app)
    {
        //Nothing
    }
}
