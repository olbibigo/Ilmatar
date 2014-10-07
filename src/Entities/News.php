<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\Exception\TranslatedException;
use Ilmatar\BaseEntity;

/**
 * News
 */
class News extends BaseEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $body;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @var string
     */
    private $created_by;

    /**
     * @var string
     */
    private $updated_by;


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
     * Set subject
     *
     * @param string $subject
     * @return News
     */
    public function setSubject($subject)
    {
        $this->subject = strip_tags($subject);

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return News
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return News
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
     * @return News
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
     * Set created_by
     *
     * @param string $createdBy
     * @return News
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
     * @return News
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
    public function assertValidNews()
    {
        if (empty($this->subject)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Subject'));
        }
        if (empty($this->body)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Body'));
        }
    }
}
