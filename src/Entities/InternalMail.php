<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;

/**
 * InternalMail
 */
class InternalMail extends BaseEntity
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
    private $read_at;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \Entities\User
     */
    private $from;

    /**
     * @var \Entities\User
     */
    private $to;


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
     * @return InternalMail
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
     * @return InternalMail
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
     * Set read_at
     *
     * @param \DateTime $readAt
     * @return InternalMail
     */
    public function setReadAt($readAt)
    {
        $this->read_at = $readAt;

        return $this;
    }

    /**
     * Get read_at
     *
     * @return \DateTime 
     */
    public function getReadAt()
    {
        return $this->read_at;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return InternalMail
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
     * Set from
     *
     * @param \Entities\User $from
     * @return InternalMail
     */
    public function setFrom(\Entities\User $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get from
     *
     * @return \Entities\User 
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set to
     *
     * @param \Entities\User $to
     * @return InternalMail
     */
    public function setTo(\Entities\User $to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Get to
     *
     * @return \Entities\User 
     */
    public function getTo()
    {
        return $this->to;
    }
    /**
     * @ORM\PrePersist
     */
    public function assertValidInternalMail()
    {
        // Add your code here
    }
}
