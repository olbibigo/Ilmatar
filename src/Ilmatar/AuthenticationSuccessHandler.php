<?php
namespace Ilmatar;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Carbon\Carbon;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    protected $app;

    public function __construct(Application $app)
    {
        parent::__construct($app['security.http_utils'], $app['security.firewalls']['secured']['form']);
        $this->app = $app;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        //Stores login timestamp
        $user = $this->app['security']->getToken()->getUser();
        $user->setLoginAt(Carbon::now());
        $this->app['orm.em']->persist($user);
        $this->app['orm.em']->flush();
        //IP blacklist
        $this->app['ip.removeFromBlacklist']($request->getClientIp());
        
        return parent::onAuthenticationSuccess($request, $token);
    }
}
