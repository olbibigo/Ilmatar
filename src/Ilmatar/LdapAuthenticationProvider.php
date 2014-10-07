<?php

namespace Ilmatar;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
 
class LdapAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $providerKey;
    private $ldap;
 
    public function __construct($userProvider, $providerKey, $ldap)
    {
        $this->userProvider = $userProvider;
        $this->providerKey  = $providerKey;
        $this->ldap         = $ldap;
    }
 
    /**
     * {@inheritdoc}
     */
    protected function retrieveUser($username, UsernamePasswordToken $token)
    {
        $user = $token->getUser();
        if ($user instanceof \Entities\User) {
            return $user;
        }
 
        try {
            $user = $this->userProvider->loadUserByUsernameAndPassword(
                $user,
                $token->getCredentials(),
                $this->ldap
            );
            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
            }
            return $user;
        } catch (UsernameNotFoundException $notFound) {
            throw $notFound;
        }
    }
 
    public function supports(TokenInterface $token)
    {
        return $token instanceof UsernamePasswordToken;
    }
 
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }
 
        $username = $token->getUsername();
        if (empty($username)) {
            $username = 'NONE_PROVIDED';
        }
 
        try {
            $user = $this->retrieveUser($username, $token);
        } catch (UsernameNotFoundException $notFound) {
            throw new BadCredentialsException('Bad credentials', 0, $notFound);
        }
        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('retrieveUser() must return a UserInterface.');
        }
 
        /*try {
            $this->checkAuthentication($user, $token);
        } catch (BadCredentialsException $e) {
            if ($this->hideUserNotFoundExceptions) {
                throw new BadCredentialsException('Bad credentials', 0, $e);
            }
 
            throw $e;
        }*/
 
        $authenticatedToken = new UsernamePasswordToken(
            $user,
            $token->getCredentials(),
            $user->getRoles()
        );

        return $authenticatedToken;
    }
}
