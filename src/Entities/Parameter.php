<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;
use Ilmatar\Exception\TranslatedException;

/**
 * Parameter
 */
class Parameter extends BaseEntity
{
    const ANALYTICS_SNIPPET        = 'ANALYTICS_SNIPPET';
   
    //List of possible types
    const TYPE_BOOLEAN  = 0;
    const TYPE_STRING   = 1;
    const TYPE_INTEGER  = 2;
    const TYPE_FLOAT    = 3;
    const TYPE_ENUM     = 4;
   
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $value;

    /**
     * @var boolean
     */
    private $is_readonly = false;

    /**
     * @var integer
     */
    private $type;

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
     * Set category
     *
     * @param string $category
     * @return Parameter
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Parameter
     */
    public function setValue($value)
    {
        if (is_null($this->type)) {
            throw new \Exception(sprintf("setType() must be called before setting value into ", __FUNCTION__));
        }
        $this->value = self::convertToStringViaType($value, $this->type, $this->code);

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        if (is_null($this->type)) {
            throw new \Exception(sprintf("setType() must be called before getting value into ", __FUNCTION__));
        }
        return self::convertToTypeFromString($this->value, $this->type, $this->code);
    }

    /**
     * Set is_readonly
     *
     * @param boolean $isReadonly
     * @return Parameter
     */
    public function setIsReadonly($isReadonly)
    {
        $this->is_readonly = $isReadonly;

        return $this;
    }

    /**
     * Get is_readonly
     *
     * @return boolean 
     */
    public function getIsReadonly()
    {
        return $this->is_readonly;
    }
    /**
     * @var \DateTime
     */
    private $updated_at;


    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Parameter
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
     * @var \DateTime
     */
    private $created_at;


    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Parameter
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
     * @var string
     */
    private $code;


    /**
     * Set code
     *
     * @param string $code
     * @return Parameter
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
     * @var string
     */
    private $created_by;

    /**
     * @var string
     */
    private $updated_by;


    /**
     * Set created_by
     *
     * @param string $createdBy
     * @return Parameter
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
     * @return Parameter
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
     * @ORM\PrePersist
     */    
    public function assertValidParameter()
    {
        if (empty($this->code)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Code'));
        }
        if (!in_array($this->type, array_keys(self::getAllTypes()))) {
            throw new TranslatedException('The field "%s" must be valid.', array('trans:Type'));
        }
    }
    
    public static function getAllTypes()
    {
        return array(
            self::TYPE_BOOLEAN  => 'Boolean',
            self::TYPE_STRING   => 'String',
            self::TYPE_INTEGER  => 'Integer',
            self::TYPE_FLOAT    => 'Float',
            self::TYPE_ENUM     => 'Enum'
        );
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Parameter
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
    
    public static function convertToStringViaType($value, $type, $code = null)
    {
        switch($type) {
            case self::TYPE_BOOLEAN:
                return in_array($value, [true, "true", "1", 1]) ? "1" : "0";
            case self::TYPE_STRING:
                return $value;
            case self::TYPE_INTEGER:
                return sprintf("%s", intval($value));
            case self::TYPE_FLOAT:
                return sprintf("%s", floatval($value));
            case self::TYPE_ENUM:
                return sprintf("%s", intval($value));
            default:
                throw new \Exception(sprintf("Invalid data type %s into %s", $type, __FUNCTION__));
        }
    }
    
    public static function convertToTypeFromString($value, $type, $code = null)
    {
        switch($type) {
            case self::TYPE_BOOLEAN:
                return ("0" == $value) ? false : true;
            case self::TYPE_STRING:
                return $value;
            case self::TYPE_INTEGER:
                return intval($value);
            case self::TYPE_FLOAT:
                return floatval($value);
            case self::TYPE_ENUM:
                return intval($value);
            default:
                throw \Exception(sprintf("Invalid data type %s into %s", $type, __FUNCTION__));
        }
    }
}
