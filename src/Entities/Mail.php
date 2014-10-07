<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;
use Carbon\Carbon;
use Ilmatar\Exception\TranslatedException;

/**
 * Mail
 */
class Mail extends BaseEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $object;

    /**
     * @var string
     */
    private $body;

    /**
     * @var boolean
     */
    private $is_error = false;

    /**
     * @var integer
     */
    private $attempt_count = 0;

    /**
     * @var \DateTime
     */
    private $sent_at;

    /**
     * @var \DateTime
     */
    private $created_at;


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
     * Set object
     *
     * @param string $object
     * @return Mail
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * Get object
     *
     * @return string
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Mail
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
     * Set is_error
     *
     * @param boolean $isError
     * @return Mail
     */
    public function setIsError($isError)
    {
        $this->is_error = $isError;

        return $this;
    }

    /**
     * Get is_error
     *
     * @return boolean
     */
    public function getIsError()
    {
        return $this->is_error;
    }

    /**
     * Set attempt_count
     *
     * @param integer $tentativeCount
     * @return Mail
     */
    public function setAttemptCount($attemptCount)
    {
        $this->attempt_count = $attemptCount;

        return $this;
    }

    /**
     * Get attempt_count
     *
     * @return integer
     */
    public function getAttemptCount()
    {
        return $this->attempt_count;
    }

    /**
     * Set sent_at
     *
     * @param \DateTime $sentAt
     * @return Mail
     */
    public function setSentAt($sentAt)
    {
        $this->sent_at = $sentAt;

        return $this;
    }

    /**
     * Get sent_at
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sent_at;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Mail
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
     * @ORM\PrePersist
     */
    public function assertValidMail()
    {
        if (false ===  filter_var($this->recipient, FILTER_VALIDATE_EMAIL)) {
            throw new TranslatedException('The field "%s" must be an email.', array('trans:Recipient'));
        }
        if (empty($this->object)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Object'));
        }
        if (empty($this->body)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Body'));
        }
    }

    public function markAsInvalid($maxAttemptCount)
    {
        $this->sent_at        = null;
        $this->attempt_count += 1;

        if ($this->attempt_count >= $maxAttemptCount) {
            $this->is_error = true;
        }
    }

    public function markAsSent()
    {
        $this->sent_at        = Carbon::now();
        $this->attempt_count += 1;
    }
    /**
     * @var string
     */
    private $recipient;


    /**
     * Set recipient
     *
     * @param string $recipient
     * @return Mail
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient
     *
     * @return string 
     */
    public function getRecipient()
    {
        return $this->recipient;
    }
}
