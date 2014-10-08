<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;
use Ilmatar\Exception\TranslatedException;
use Ilmatar\Doctrine\Listeners\AuditableInterface;

/**
 * Pipo
 */
class Pipo extends BaseEntity implements AuditableInterface
{
    public static $allowedHttpMethodForWebservice = [
        \Project\Controller\WebserviceController::HTTP_METHOD_GET,
        \Project\Controller\WebserviceController::HTTP_METHOD_POST,
        \Project\Controller\WebserviceController::HTTP_METHOD_PUT,
        \Project\Controller\WebserviceController::HTTP_METHOD_DELETE
    ];
    public static $allowedFieldsToImport = ['value', 'user.city','thedatetime_at', 'thetype_date','functionality.code', 'email', 'mycheck'];

    /**
     * @var integer
     */
    private $id;

    /**
     * @var float
     */
    private $value;

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
     * Set value
     *
     * @param float $value
     * @return Pipo
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set user
     *
     * @param \Entities\User $user
     * @return Pipo
     */
    public function setUser(\Entities\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Entities\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @var \Entities\User
     */
    private $user;
    /**
     * @var \DateTime
     */
    private $thedatetime_at;

    /**
     * @var \DateTime
     */
    private $thetype_date;


    /**
     * Set thedatetime_at
     *
     * @param \DateTime $thedatetimeAt
     * @return Pipo
     */
    public function setThedatetimeAt($thedatetimeAt)
    {
        $this->thedatetime_at = $thedatetimeAt;

        return $this;
    }

    /**
     * Get thedatetime_at
     *
     * @return \DateTime 
     */
    public function getThedatetimeAt()
    {
        return $this->thedatetime_at;
    }

    /**
     * Set thetype_date
     *
     * @param \DateTime $thetypeDate
     * @return Pipo
     */
    public function setThetypeDate($thetypeDate)
    {
        $this->thetype_date = $thetypeDate;

        return $this;
    }

    /**
     * Get thetype_date
     *
     * @return \DateTime 
     */
    public function getThetypeDate()
    {
        return $this->thetype_date;
    }
    /**
     * @var \Entities\Functionality
     */
    private $functionality;


    /**
     * Set functionality
     *
     * @param \Entities\Functionality $functionality
     * @return Pipo
     */
    public function setFunctionality(\Entities\Functionality $functionality = null)
    {
        $this->functionality = $functionality;

        return $this;
    }

    /**
     * Get functionality
     *
     * @return \Entities\Functionality 
     */
    public function getFunctionality()
    {
        return $this->functionality;
    }
    /**
     * @ORM\PrePersist
     */
    public function assertValidPipo()
    {
        if (($this->mycheck != 0)
            && ($this->mycheck != 1)) {
            throw new TranslatedException('The field "%s" must be valid.', array('trans:Mycheck'));
        }
        if (!is_null($this->email)  && false ===  filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new TranslatedException('The field "%s" must be an email.', array('trans:Email'));
        }
        if (!is_null($this->thedatetime_at) && $this->thedatetime_at <= $this->thetype_date) {
            throw new TranslatedException('"%s" cannot be sooner than "%s".', array("trans:Thedatetime at", "trans:Thetype date"));
        }
        if (is_null($this->value)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Value'));
        }
        if (is_null($this->user)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:User'));
        }
    }
    
    
    /**
     * @var string
     */
    private $email;


    /**
     * Set email
     *
     * @param string $email
     * @return Pipo
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * @var integer
     */
    private $check;


    /**
     * Set check
     *
     * @param integer $check
     * @return Pipo
     */
    public function setCheck($check)
    {
        $this->check = $check;

        return $this;
    }

    /**
     * Get check
     *
     * @return integer 
     */
    public function getCheck()
    {
        return $this->check;
    }
    /**
     * @var integer
     */
    private $mycheck;


    /**
     * Set mycheck
     *
     * @param integer $mycheck
     * @return Pipo
     */
    public function setMycheck($mycheck)
    {
        $this->mycheck = $mycheck;

        return $this;
    }

    /**
     * Get mycheck
     *
     * @return integer 
     */
    public function getMycheck()
    {
        return $this->mycheck;
    }
    /**
     * @var encryptedstring
     */
    private $crypto;


    /**
     * Set crypto
     *
     * @param encryptedstring $crypto
     * @return Pipo
     */
    public function setCrypto($crypto)
    {
        $this->crypto = $crypto;

        return $this;
    }

    /**
     * Get crypto
     *
     * @return encryptedstring 
     */
    public function getCrypto()
    {
        return $this->crypto;
    }
}
