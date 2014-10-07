<?php

namespace Ilmatar;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Ilmatar\Exception\TranslatedException;
use Symfony\Component\Validator\Constraints\Callback;

class DbUser extends BaseUser
{
    const MIN_PASSWORD_LENGTH = 8;
    
    /*
     * Complexe validatePassword
     */
    public function validatePassword(ExecutionContextInterface $context)
    {
        $password = $this->getPassword();

        //New user must have a password
        if (JqGrid::ID_NEW_ENTITY == $this->getId()) {
            if (is_null($password) || empty($password)) {
                $context->addViolationAt(
                    'password',
                    sprintf('Password length must be valid.'),
                    [],
                    null
                );
                return;
            }
        }
        //Existing user can leave the password empty but if set must comply with rule.
        if (!is_null($password) && !empty($password)) {
            if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
                $context->addViolationAt(
                    'password',
                    sprintf('Password length must be valid.'),
                    [],
                    null
                );
            }
        }
    }

    public function assertValidUser()
    {
        if (false ===  filter_var($this->username, FILTER_VALIDATE_EMAIL)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Email'));
        }
        
        if (empty($this->password)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Password'));
        }
    }
    
    /*
     * Checks related form (see class Project\Form\UserType)
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new Callback('validatePassword'));
    }
}
