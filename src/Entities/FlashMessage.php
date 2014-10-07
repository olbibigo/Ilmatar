<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;
use Carbon\Carbon;
use Ilmatar\Exception\TranslatedException;

/**
 * FlashMessage
 */
class FlashMessage extends BaseEntity
{
    const TARGET_ALL        = 0;
    const TARGET_ONLY_USERS = 1;
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $is_active = true;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $body;

    /**
     * @var integer
     */
    private $target = self::TARGET_ONLY_USERS;

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
     * Set is_active
     *
     * @param boolean $isActive
     * @return FlashMessage
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
     * Set subject
     *
     * @param string $subject
     * @return FlashMessage
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
     * @return FlashMessage
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
     * Set target
     *
     * @param integer $target
     * @return FlashMessage
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return integer
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return FlashMessage
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
     * @return FlashMessage
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
     * @return FlashMessage
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
     * @return FlashMessage
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
    public function assertValidFlashMessage()
    {
        if (empty($this->subject)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Subject'));
        }
        if (empty($this->body)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Body'));
        }
        if (is_null($this->begin_at)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Begin at'));
        }
        if (is_null($this->end_at)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:End at'));
        }
        if ($this->end_at <= $this->begin_at) {
            throw new TranslatedException('"%s" cannot be sooner than "%s".', array('trans:Begin at', "trans:End at"));
        }
        if (!in_array($this->target, array_keys(self::getAllTargets()))) {
            throw new TranslatedException('The field "%s" must be valid.', array('trans:Target'));
        }
    }

    public static function getAllTargets()
    {
        return array(
            self::TARGET_ALL        => 'All visitors',
            self::TARGET_ONLY_USERS => 'Only users'
        );
    }
    /**
     * @var \DateTime
     */
    private $begin_at;

    /**
     * @var \DateTime
     */
    private $end_at;


    /**
     * Set begin_at
     *
     * @param \DateTime $beginAt
     * @return FlashMessage
     */
    public function setBeginAt($beginAt)
    {
        $this->begin_at = $beginAt;

        return $this;
    }

    /**
     * Get begin_at
     *
     * @return \DateTime
     */
    public function getBeginAt()
    {
        return $this->begin_at;
    }

    /**
     * Set end_at
     *
     * @param \DateTime $endAt
     * @return FlashMessage
     */
    public function setEndAt($endAt)
    {
        $this->end_at = $endAt;

        return $this;
    }

    /**
     * Get end_at
     *
     * @return \DateTime
     */
    public function getEndAt()
    {
        return $this->end_at;
    }
}
