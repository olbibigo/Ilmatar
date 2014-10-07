<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;

/**
 * Audit
 */
class Audit extends BaseEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $entity_type;

    /**
     * @var integer
     */
    private $entity_id;

    /**
     * @var string
     */
    private $property_name;

    /**
     * @var string
     */
    private $old_value;

    /**
     * @var string
     */
    private $new_value;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var string
     */
    private $created_by;


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
     * Set entity_type
     *
     * @param string $entityType
     * @return Audit
     */
    public function setEntityType($entityType)
    {
        $this->entity_type = $entityType;

        return $this;
    }

    /**
     * Get entity_type
     *
     * @return string 
     */
    public function getEntityType()
    {
        return $this->entity_type;
    }

    /**
     * Set entity_id
     *
     * @param integer $entityId
     * @return Audit
     */
    public function setEntityId($entityId)
    {
        $this->entity_id = $entityId;

        return $this;
    }

    /**
     * Get entity_id
     *
     * @return integer 
     */
    public function getEntityId()
    {
        return $this->entity_id;
    }

    /**
     * Set property_name
     *
     * @param string $propertyName
     * @return Audit
     */
    public function setPropertyName($propertyName)
    {
        $this->property_name = $propertyName;

        return $this;
    }

    /**
     * Get property_name
     *
     * @return string 
     */
    public function getPropertyName()
    {
        return $this->property_name;
    }

    /**
     * Set old_value
     *
     * @param string $oldValue
     * @return Audit
     */
    public function setOldValue($oldValue)
    {
        $this->old_value = $oldValue;

        return $this;
    }

    /**
     * Get old_value
     *
     * @return string 
     */
    public function getOldValue()
    {
        return $this->old_value;
    }

    /**
     * Set new_value
     *
     * @param string $newValue
     * @return Audit
     */
    public function setNewValue($newValue)
    {
        $this->new_value = $newValue;

        return $this;
    }

    /**
     * Get new_value
     *
     * @return string 
     */
    public function getNewValue()
    {
        return $this->new_value;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Audit
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
     * Set created_by
     *
     * @param string $createdBy
     * @return Audit
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
     * @ORM\PrePersist
     */
    public function assertValidAudit()
    {
        // Add your code here
    }
}
