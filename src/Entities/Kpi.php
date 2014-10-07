<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;

/**
 * Kpi
 */
class Kpi extends BaseEntity
{

    const NB_USERS = "NB_USERS";
    //Add kpi here

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var boolean
     */
    private $is_active = true;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $class;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $values;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $roles;
    /**
     * Constructor
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->values = new \Doctrine\Common\Collections\ArrayCollection();
        $this->roles  = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Kpi
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
     * Set is_active
     *
     * @param boolean $isActive
     * @return Kpi
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get is_active
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Kpi
     */
    public function setDescription($description)
    {
        $this->description = strip_tags($description);

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set class
     *
     * @param string $class
     * @return Kpi
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Add values
     *
     * @param \Entities\KpiValue $values
     * @return Kpi
     */
    public function addValue(\Entities\KpiValue $values)
    {
        $this->values[] = $values;

        return $this;
    }

    /**
     * Remove values
     *
     * @param \Entities\KpiValue $values
     */
    public function removeValue(\Entities\KpiValue $values)
    {
        $this->values->removeElement($values);
    }

    /**
     * Get values
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Add roles
     *
     * @param \Entities\Role $roles
     * @return Kpi
     */
    public function addRole(\Entities\Role $role)
    {
        // synchronously updating inverse side
        $role->addKpi($this);
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param \Entities\Role $role
     */
    public function removeRole(\Entities\Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
