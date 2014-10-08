<?php
namespace Ilmatar;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    
    protected $app;

    public function __construct(
        HttpKernelInterface $httpKernel,
        Application $app
    ) {
        parent::__construct($httpKernel, $app['security.http_utils'], $app['security.firewalls']['secured']['form'], $app['logger']);
        $this->app = $app;
    }
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        //Logs login attempt
        //IP blacklist
        $this->app['ip.manageLoginAttempt']($request->getClientIp());
        
        return parent::onAuthenticationFailure($request, $exception);
    }
}
