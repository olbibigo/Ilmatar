<?php

namespace Ilmatar\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class LdapServiceProvider implements ServiceProviderInterface
{
    protected $ds;

    protected function initConnection($app)
    {
        $this->ds = ldap_connect($app['ldap']['host']);
        ldap_set_option($this->ds, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_start_tls($this->ds);
        ldap_bind(
            $this->ds,
            $app['ldap']['username'],
            $app['ldap']['password']
        );
    }

    public function register(Application $app)
    {
        $app['ldap.authenticate'] = $app->protect(function ($username, $password) use ($app) {
            if (!isset($this->ds)) {
                $this->initConnection($app);
            }
            try {
                //Search of user trying to login
                $search = ldap_search(
                    $this->ds,
                    $app['ldap']['baseDN'],
                    sprintf('(&(objectCategory=person)(sAMAccountName=%s))', $username),
                    ["objectGUID", "mail", 'sn', 'givenName']
                );
                $userinfo = ldap_get_entries($this->ds, $search)[0];
                //Provided password validation
                ldap_bind(
                    $this->ds,
                    $userinfo['dn'],
                    $password
                );
                return $userinfo;
            } catch (\Exception $e) {
                throw new UsernameNotFoundException(
                    sprintf('Unable to find an active admin user object identified by "%s".', $username)
                );
            }
        });
    }

    public function boot(Application $app)
    {
        $app->finish(function () {
            if (isset($this->ds)) {
                ldap_close($this->ds);
            }
        });
    }
}
