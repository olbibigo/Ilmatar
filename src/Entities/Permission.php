<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;
use Ilmatar\Exception\TranslatedException;

/**
 * Permission
 */
class Permission extends BaseEntity
{
    //Test PHPUnit ImportHelper, name don't exist.
    public static $allowedFieldsToImport = ['type.code'];
    
    const ACCESS_READ      = 0;
    const ACCESS_READWRITE = 1;
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $type = self::ACCESS_READ;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Permission
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @var \Entities\Role
     */
    private $role;

    /**
     * @var \Entities\Functionality
     */
    private $functionality;


    /**
     * Set role
     *
     * @param \Entities\Role $role
     * @return Permission
     */
    public function setRole(\Entities\Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Entities\Role 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set functionality
     *
     * @param \Entities\Functionality $functionality
     * @return Permission
     */
    public function setFunctionality(\Entities\Functionality $functionality = null)
    {
        $this->functionality = $functionality;

        return $this;
    }

    /**
     * Get functionality
     *
     * @return \Entities\Functionality 
     */
    public function getFunctionality()
    {
        return $this->functionality;
    }

    public function setReadOn()
    {
        $this->type = self::ACCESS_READ;
    }
    
    public function setWriteOn()
    {
        $this->type = self::ACCESS_READWRITE;
    }
    public function isWriteOn()
    {
        return ($this->type == self::ACCESS_READWRITE);
    }
    public function isReadOn()
    {
        //if this perm exists, there is at least the R right
        return true;
    }
    public function assertValidPermission()
    {
        if (!in_array($this->type, array_keys(self::getAllTypes()))) {
            throw new TranslatedException('The field "%s" must be valid.', array('trans:Type'));
        }
    }
    public static function getAllTypes()
    {
        return array(
            self::ACCESS_READ      => 'Read',
            self::ACCESS_READWRITE => 'Read + Write'
        );
    }
}
