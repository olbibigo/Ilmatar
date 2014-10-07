<?php
namespace Ilmatar\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class NotificationServiceProvider implements ServiceProviderInterface
{
    const RESET_MODE_NONE               = 'none';
    const RESET_MODE_ONLY_TYPE          = 'only_type';
    const RESET_MODE_ONLY_LESS_PRIORITY = 'only_less_priority';
    const RESET_MODE_ALL                = 'all';
    
    public function register(Application $app)
    {
        //Inspired by https://github.com/php-fig/fig-standards/blob/master/accepted/fr/PSR-3-logger-interface.md
        $app['notification.types'] = [
            'emergency',/*Top priority*/
            'alert',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
            'success', /*WARNING: this type not in PSR3*/
            'debug' /*Bottom priority*/
        ];

        $app['notification.resetModes'] = [
            self::RESET_MODE_NONE,
            self::RESET_MODE_ONLY_TYPE,
            self::RESET_MODE_ONLY_LESS_PRIORITY,
            self::RESET_MODE_ALL,
        ];

        $app['notification'] = $app->protect(function ($message, $type = 'info', $resetMode = self::RESET_MODE_NONE) use ($app) {
            if (!in_array($type, $app['notification.types'], true)) {
                throw new \Exception(sprintf("Invalid notification type '%s' for %s().", $type, __FUNCTION__));
            }
            if (!in_array($resetMode, $app['notification.resetModes'], true)) {
                throw new \Exception(sprintf("Invalid rset mode '%s' for %s().", $type, __FUNCTION__));
            }
            if (isset($app['session'])) {
                $flashs = $app['session']->getFlashBag();
                if (self::RESET_MODE_ONLY_TYPE == $resetMode) {
                    $flashs->get($type);
                } elseif (self::RESET_MODE_ALL == $resetMode) {
                    $flashs->clear();
                } elseif (self::RESET_MODE_ONLY_LESS_PRIORITY == $resetMode) {
                    $isFound = false;
                    foreach ($app['notification.types'] as $notificationType) {
                        if ($isFound) {
                            $flashs->get($notificationType);
                            continue;
                        }
                        if ($type == $notificationType) {
                            $isFound = true;
                        }
                    }
                }
                $flashs->add($type, $message);
            }
        });
        $app['scheduled_notification'] = $app->protect(function ($target) use ($app) {
            $flashMessages = $app['orm.em']->getRepository('\\Entities\\FlashMessage')->getMessagesToDisplay($target);
            if (!is_null($flashMessages)) {
                foreach ($flashMessages as $flashMessage) {
                    $key = 'flash_displayed_' . $flashMessage['id'];
                    //Display flash messages only once per session
                    if (!$app['session']->has($key)) {
                        $app['session']->set($key, true);
                        $app['notification'](
                            sprintf(
                                "<b>%s</b><br/><br/><div style=\"text-align:left\">%s</div>",
                                $flashMessage['subject'],
                                $flashMessage['body']
                            ),
                            'notice'
                        );
                    }
                }
            }
        });
    }

    public function boot(Application $app)
    {
        //Nothing
    }
}
