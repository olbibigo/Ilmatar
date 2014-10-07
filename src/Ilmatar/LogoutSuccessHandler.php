<?php
namespace Ilmatar;

use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Carbon\Carbon;

class LogoutSuccessHandler extends DefaultLogoutSuccessHandler
{
    protected $app;

    public function __construct(Application $app, $targetUrl = '/')
    {
        parent::__construct($app['security.http_utils'], $targetUrl);
        $this->app = $app;
    }

    public function onLogoutSuccess(Request $request)
    {
        if (!is_null($this->app['security']->getToken())) {
            //Stores logout timestamp
            $user = $this->app['security']->getToken()->getUser();
            $user->setLogoutAt(Carbon::now());
            $this->app['orm.em']->persist($user);
            $this->app['orm.em']->flush();
        }
        return parent::onLogoutSuccess($request);
    }
}
