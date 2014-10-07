<?php

namespace Entities;

use Ilmatar\BaseEntity;
use Ilmatar\Exception\TranslatedException;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Callback;

/**
 * Query
 */
class Query extends BaseEntity
{
    const VISIBILITY_ONLY_CREATOR = 0; //Nevertheless not applied to ADMIN role who see them
    const VISIBILITY_ALL          = 1;
    
    const REPEAT_DAILY   = 0;//no offset
    const REPEAT_WEEKLY  = 1;//offset expected as weekday (from 0 to 6)
    const REPEAT_MONTHLY = 2;//offset expected as day (from 1 to 31)
    
    const FORMAT_CSV = 0;
    const FORMAT_PDF = 1;
    const FORMAT_XLS = 2;
    const FORMAT_XML = 3;
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $comment = '';

    /**
     * @var string
     */
    private $query;

    /**
     * @var integer
     */
    private $visibility = self::VISIBILITY_ALL;

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
     * @var \Entities\User
     */
    private $creator;

    /**
     * @var boolean
     */
    private $is_visible = true;

    /**
     * @var boolean
     */
    private $is_exported = false;

    /**
     * @var string
     */
    private $mail_list;

    /**
     * @var integer
     */
    private $mail_repeats = self::REPEAT_DAILY;

    /**
     * @var integer
     */
    private $mail_offset = 0;

    /**
     * @var integer
     */
    private $export_format = self::FORMAT_PDF;

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
     * Set name
     *
     * @param string $name
     * @return Query
     */
    public function setName($name)
    {
        $this->name = strip_tags($name);

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Query
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set query
     *
     * @param string $query
     * @return Query
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query
     *
     * @return string 
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set visibility
     *
     * @param integer $visibility
     * @return Query
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get visibility
     *
     * @return integer 
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * @return Query
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
     * Set creator
     *
     * @param \Entities\User $creator
     * @return Query
     */
    public function setCreator(\Entities\User $creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \Entities\User 
     */
    public function getCreator()
    {
        return $this->creator;
    }
    
    public static function getAllVisibilities()
    {
        return array(
            self::VISIBILITY_ONLY_CREATOR => 'Only creator',
            self::VISIBILITY_ALL          => 'All users'
        );
    }

    /**
     * @ORM\PrePersist
     */
    public function assertValidQuery()
    {
        if (empty($this->name)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Name'));
        }
        if (empty($this->query)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Query'));
        }
        if (!in_array($this->visibility, array_keys(self::getAllVisibilities()))) {
            throw new TranslatedException('The field "%s" must be valid.', array('trans:Visibility'));
        }
        if (!in_array($this->export_format, array_keys(self::getAllExportFormats()))) {
            throw new TranslatedException('The field "%s" must be valid.', array('trans:Export format'));
        }
        if (!in_array($this->mail_repeats, array_keys(self::getAllRepeats()))) {
            throw new TranslatedException('The field "%s" must be valid.', array('trans:Mail repeats'));
        }
        switch ($this->mail_repeats) {
            case self::REPEAT_WEEKLY:
                if (($this->mail_offset < 0) || ($this->mail_offset > 6)) {
                    throw new TranslatedException('The field "%s" must be valid.', array('trans:Mail offset'));
                }
                break;
            case self::REPEAT_MONTHLY:
                if (($this->mail_offset <= 0) || ($this->mail_offset > 31)) {
                    throw new TranslatedException('The field "%s" must be valid.', array('trans:Mail offset'));
                }
                break;
            case self::REPEAT_DAILY:
            default:
                //Nothing
        }
    }

    /*
     * Checks related form (see class Project\Form\RoleType)
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new NotBlank());
        $metadata->addPropertyConstraint('query', new NotBlank());
        $metadata->addPropertyConstraint('query', new Regex(['pattern' => '/^SELECT/i']));
        $metadata->addPropertyConstraint('visibility', new Choice(array_keys(self::getAllVisibilities())));
        $metadata->addPropertyConstraint('mail_repeats', new Choice(array_keys(self::getAllRepeats())));
        $metadata->addPropertyConstraint('export_format', new Choice(array_keys(self::getAllExportFormats())));
        $metadata->addConstraint(new Callback('validateOffset'));
    }

    public function validateOffset(ExecutionContextInterface $context)
    {
        $mailOffset = $this->getMailOffset();
        switch ($this->getMailRepeats()) {
            case self::REPEAT_WEEKLY:
                if (($mailOffset < 0) || ($mailOffset > 6)) {
                    $context->addViolationAt(
                        'mail_offset',
                        sprintf("The field must be valid."),
                        [],
                        null
                    );
                }
                break;
            case self::REPEAT_MONTHLY:
                if (($mailOffset <= 0) || ($mailOffset > 31)) {
                    $context->addViolationAt(
                        'mail_offset',
                        sprintf("The field must be valid."),
                        [],
                        null
                    );
                }
                break;
            case self::REPEAT_DAILY:
            default:
                //Nothing
        }
    }
    public function isAllowedUser(\Entities\user $user)
    {
        return $user->isAdmin()
            || ($this->visibility == self::VISIBILITY_ALL)
            || ($this->creator == $user);
    }

    /**
     * Set is_visible
     *
     * @param boolean $isVisible
     * @return Query
     */
    public function setIsVisible($isVisible)
    {
        $this->is_visible = $isVisible;

        return $this;
    }

    /**
     * Get is_visible
     *
     * @return boolean 
     */
    public function getIsVisible()
    {
        return $this->is_visible;
    }

    /**
     * Set is_exported
     *
     * @param boolean $isExported
     * @return Query
     */
    public function setIsExported($isExported)
    {
        $this->is_exported = $isExported;

        return $this;
    }

    /**
     * Get is_exported
     *
     * @return boolean 
     */
    public function getIsExported()
    {
        return $this->is_exported;
    }

    /**
     * Set mail_list
     *
     * @param string $mailList
     * @return Query
     */
    public function setMailList($mailList)
    {
        $this->mail_list = $mailList;

        return $this;
    }

    /**
     * Get mail_list
     *
     * @return string 
     */
    public function getMailList()
    {
        return $this->mail_list;
    }

    /**
     * Set mail_repeats
     *
     * @param integer $mailRepeats
     * @return Query
     */
    public function setMailRepeats($mailRepeats)
    {
        if (!is_null($mailRepeats)) {
            $this->mail_repeats = $mailRepeats;
        }
        return $this;
    }

    /**
     * Get mail_repeats
     *
     * @return integer 
     */
    public function getMailRepeats()
    {
        return $this->mail_repeats;
    }

    /**
     * Set mail_offset
     *
     * @param integer $mailOffset
     * @return Query
     */
    public function setMailOffset($mailOffset)
    {
        $this->mail_offset = $mailOffset;

        return $this;
    }

    /**
     * Get mail_offset
     *
     * @return integer 
     */
    public function getMailOffset()
    {
        return $this->mail_offset;
    }
    
    public static function getAllRepeats()
    {
        return array(
            self::REPEAT_DAILY   => 'Everyday',
            self::REPEAT_WEEKLY  => 'Every week',
            self::REPEAT_MONTHLY => 'Every month'
        );
    }

    public static function getAllExportFormats()
    {
        return array(
            self::FORMAT_CSV => 'CSV',
            self::FORMAT_PDF => 'PDF',
            self::FORMAT_XLS => 'XLS',
            self::FORMAT_XML => 'XML'
        );
    }

    /**
     * Set export_format
     *
     * @param integer $exportFormat
     * @return Query
     */
    public function setExportFormat($exportFormat)
    {
        if (!is_null($exportFormat)) {
            $this->export_format = $exportFormat;
        }
        return $this;
    }

    /**
     * Get export_format
     *
     * @return integer 
     */
    public function getExportFormat()
    {
        return $this->export_format;
    }
}
