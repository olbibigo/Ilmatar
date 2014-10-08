<?php

namespace Entities;

use Ilmatar\BaseEntity;
use Ilmatar\Exception\TranslatedException;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Role
 */
class Role extends BaseEntity
{
    //Test PHPUnit ImportHelper, name don't exist.
    public static $allowedFieldsToImport = ['name', 'code'];
    
    const FUNCTIONAL_ADMIN_CODE = 'FUNCTIONAL_ADMIN';
    const TECHNICAL_ADMIN_CODE  = 'TECHNICAL_ADMIN';
    const BASIC_CODE            = 'BASIC';
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @var \DateTime
     */
    private $deleted_at;

    /**
     * @var string
     */
    private $created_by;

    /**
     * @var string
     */
    private $updated_by;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $permissions;

    /**
     * Constructor
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Role
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
     * Set description
     *
     * @param string $description
     * @return Role
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Role
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Role
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set deleted_at
     *
     * @param \DateTime $deletedAt
     * @return Role
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deleted_at = $deletedAt;

        return $this;
    }

    /**
     * Get deleted_at
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

    /**
     * Set created_by
     *
     * @param string $createdBy
     * @return Role
     */
    public function setCreatedBy($createdBy)
    {
        $this->created_by = $createdBy;

        return $this;
    }

    /**
     * Get created_by
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * Set updated_by
     *
     * @param string $updatedBy
     * @return Role
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updated_by = $updatedBy;

        return $this;
    }

    /**
     * Get updated_by
     *
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updated_by;
    }

    /**
     * Add users
     *
     * @param \Entities\User $users
     * @return Role
     */
    public function addUser(\Entities\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Entities\User $users
     */
    public function removeUser(\Entities\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add permissions
     *
     * @param \Entities\Permission $permissions
     * @return Role
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
     * Get all permissions
     *
     * @return array
     */
    public function getPermissionsAsArray()
    {
        $permissions = $this->getPermissions();
        $result = [];
        if (!is_null($permissions)) {
            foreach ($permissions as $permission) {
                $result[$permission->getFunctionality()->getCode()] = $permission->getType();
            }
        }
        return $result;
    }

    /**
     * @var string
     */
    private $deleted_by;


    /**
     * Set deleted_by
     *
     * @param string $deletedBy
     * @return Role
     */
    public function setDeletedBy($deletedBy)
    {
        $this->deleted_by = $deletedBy;

        return $this;
    }

    /**
     * Get deleted_by
     *
     * @return string
     */
    public function getDeletedBy()
    {
        return $this->deleted_by;
    }
    /**
     * @ORM\PrePersist
     */
    public function assertValidRole()
    {
        if (empty($this->code)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Code'));
        }
    }
    /*
     * Checks related form (see class Project\Form\RoleType)
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('code', new NotBlank());
        $metadata->addConstraint(new UniqueEntity(['fields'  => 'code']));
    }
    
    public function __toString()
    {
        return $this->code;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $kpis;


    /**
     * Add kpis
     *
     * @param \Entities\Kpi $kpis
     * @return Role
     */
    public function addKpi(\Entities\Kpi $kpis)
    {
        $this->kpis[] = $kpis;

        return $this;
    }

    /**
     * Remove kpis
     *
     * @param \Entities\Kpi $kpis
     */
    public function removeKpi(\Entities\Kpi $kpis)
    {
        $this->kpis->removeElement($kpis);
    }

    /**
     * Get kpis
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getKpis()
    {
        return $this->kpis;
    }

    /**
     * Get kpi
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function isKpi($kpiCompare)
    {
        $kpis = $this->getKpis();
        $response = false;
        if (!is_null($kpis)) {
            foreach ($kpis as $kpi) {
                if ($kpi->getId() == $kpiCompare->getId()) {
                    $response = true;
                    break;
                }
            }
        }

        return $response;

    }

    /**
     * Get all kpi
     *
     * @return array
     */
    public function getKpisAsArray()
    {
        $kpis = $this->getKpis();
        $result = [];
        if (!is_null($kpis)) {
            foreach ($kpis as $kpi) {
                $result[$kpi->getCode()] = 1;
            }
        }
        return $result;
    }
    
    public function isAdmin()
    {
        return ($this->getCode() == self::TECHNICAL_ADMIN_CODE) || ($this->getCode() == self::FUNCTIONAL_ADMIN_CODE);
    }
}
