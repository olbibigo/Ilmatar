<?php

namespace Ilmatar;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;

class LdapAuthenticationListener extends UsernamePasswordFormAuthenticationListener implements ListenerInterface
{

}
