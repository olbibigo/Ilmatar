<?php

namespace Ilmatar;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Ilmatar\Helper\SecurityHelper;

class BaseUser extends BaseEntity implements AdvancedUserInterface, \Serializable
{
    public function serialize()
    {
        return serialize([$this->getId()]);
    }

    public function unserialize($serialized)
    {
        $this->setId(unserialize($serialized)[0]);
    }
    
    public function getRoles()
    {
        return [\Entities\Role::BASIC_CODE];
    }

    public function getPassword()
    {
    }

    public function getUsername()
    {
    }
    
    public function getSalt()
    {
        return SecurityHelper::DEFAULT_KEY;
    }

    public function eraseCredentials()
    {
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->getIsActive();
    }
    
    public function setUsername($username)
    {
    }

    public function setPassword($password)
    {
    }
    
    public function assertValidUser()
    {
    }
    
    /*
     * Checks related form (see class Project\Form\UserType)
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
    }
}
