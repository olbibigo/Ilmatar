<?php

namespace Entities;

use Ilmatar\BaseEntity;

/**
 * Functionality
 */
class Functionality extends BaseEntity
{
    const DASHBOARD        = 'DASHBOARD';
    const USER             = 'USER';
    const ROLE             = 'ROLE';
    const MAIL_TEMPLATE    = 'MAIL_TEMPLATE';
    const GLOBAL_PARAMETER = 'GLOBAL_PARAMETER';
    const EDITORIAL        = 'EDITORIAL';
    const JOB              = 'JOB';
    const QUERY            = 'QUERY';
    const LOG              = 'LOG';
    const TRANSLATION      = 'TRANSLATION';
    const FLASH_MESSAGE    = 'FLASH_MESSAGE';
    const INTERNAL_MAIL    = 'INTERNAL_MAIL';
    const IMPORT           = 'IMPORT';
    const FUNCTIONALITY    = 'FUNCTIONALITY';
    const MAINTENANCE      = 'MAINTENANCE';
    const NEWS             = 'NEWS';
    //Add functionality here

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $permissions;

    /**
     * @var boolean
     */
    private $is_editable = true;
    
    /**
     * Constructor
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set code
     *
     * @param string $code
     * @return Functionality
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Add permissions
     *
     * @param \Entities\Permission $permissions
     * @return Functionality
     */
    public function addPermission(\Entities\Permission $permissions)
    {
        $this->permissions[] = $permissions;

        return $this;
    }

    /**
     * Remove permissions
     *
     * @param \Entities\Permission $permissions
     */
    public function removePermission(\Entities\Permission $permissions)
    {
        $this->permissions->removeElement($permissions);
    }

    /**
     * Get permissions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Set is_editable
     *
     * @param boolean $isEditable
     * @return Role
     */
    public function setIsEditable($isEditable)
    {
        $this->is_editable = $isEditable;

        return $this;
    }

    /**
     * Get is_editable
     *
     * @return boolean 
     */
    public function getIsEditable()
    {
        return $this->is_editable;
    }
}
