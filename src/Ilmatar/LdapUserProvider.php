<?php

namespace Ilmatar;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class LdapUserProvider extends JqGrid implements UserProviderInterface
{

    public function loadUserByUsernameAndPassword($username, $password, $ldap)
    {
        $userinfo = $ldap($username, $password);
        $q        = $this->createQueryBuilder('u')
                         ->where('u.password = :objectguid')
                         ->setParameter('objectguid', base64_encode($userinfo['objectguid'][0]))
                         ->getQuery();
        try {
            $user = $q->getSingleResult();
        } catch (\Exception $e) {
            try {
                $user = new \Entities\User(
                    [
                        'lastname'  => $userinfo["sn"][0],
                        'firstname' => $userinfo["givenname"][0],
                        'username'  => $userinfo["mail"][0],
                        'password'  => base64_encode($userinfo["objectguid"][0]),
                        'role'      => $this->getEntityManager()->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::BASIC_CODE),
                        'created_by' => 'Ldap',
                        'updated_by' => 'Ldap'
                    ]
                );
                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }
        return $this->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

    public function loadUserByUsername($username)
    {
        return new \Entities\User();
    }
}
