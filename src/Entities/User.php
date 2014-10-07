<?php

namespace Entities;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Ilmatar\Exception\TranslatedException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Ilmatar\JqGrid;
use Ilmatar\DbUser;

/**
 * User
 */
class User extends DbUser
{
    const GENDER_MALE   = 0;
    const GENDER_FEMALE = 1;
    
    //Test PHPUnit ImportHelper, role.name don't exist.
    public static $allowedFieldsToImport = ['firstname', 'lastname', 'role.name'];
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    //Set public for base class access
    public $username;

    /**
     * @var string
     */
    //Set public for base class access
    public $password;

    /**
     * @var boolean
     */
    private $is_active = true;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var integer
     */
    private $gender = self::GENDER_MALE;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $zipcode;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $mobile;

    /**
     * Get id
     * unserialize
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    
    //This method is necessary for base class unserialize()
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
    /**
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = strip_tags($firstname);

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = strip_tags($lastname);

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set is_active
     *
     * @param boolean $isActive
     * @return User
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
     * Set comment
     *
     * @param string $comment
     * @return User
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

    /*********************************************/
    /**
     * @var \Entities\Role
     */
    private $role;


    /**
     * Set role
     *
     * @param \Entities\Role $role
     * @return User
     */
    public function setRole(\Entities\Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Entities\Role
     */
    public function getRole()
    {
        return $this->role;
    }
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * @return User
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
     * Get updated_by
     *
     * @return string
     */
    public function getFullname($isLong = false)
    {
        return ($isLong ? $this->getFirstname() : mb_substr($this->getFirstname(), 0, 1, 'utf-8') . '.' ) . ' '. $this->getLastname();
    }

    /**
     * @var string
     */
    private $deleted_by;


    /**
     * Set deleted_by
     *
     * @param string $deletedBy
     * @return User
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
     * Set gender
     *
     * @param integer $gender
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return integer
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return User
     */
    public function setStreet($street)
    {
        $this->street = strip_tags($street);

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return User
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = strip_tags($zipcode);

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return User
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return User
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = strip_tags($phone);

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return User
     */
    public function setMobile($mobile)
    {
        $this->mobile = strip_tags($mobile);

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /*
     * Checks just insert into DB
     * Messages are translated inside catch{} (See class Project\Controller\UserController)
     */
    public function assertValidUser()
    {
        parent::assertValidUser();

        if (empty($this->firstname)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Firstname'));
        }

        if (empty($this->lastname)) {
            throw new TranslatedException('The field "%s" cannot be empty.', array('trans:Lastname'));
        }

        if (!in_array($this->gender, array_keys(self::getAllGenders()))) {
            throw new TranslatedException('The field "%s" must be valid.', array('trans:Gender'));
        }
    }
    /*
     * Checks related form (see class Project\Form\UserType)
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        parent::loadValidatorMetadata($metadata);
        $metadata->addConstraint(new UniqueEntity(['fields'  => 'username']));
        $metadata->addPropertyConstraint('firstname', new NotBlank());
        $metadata->addPropertyConstraint('lastname', new NotBlank());
        $metadata->addPropertyConstraint('gender', new Choice(array_keys(self::getAllGenders())));
        $metadata->addPropertyConstraint('username', new NotBlank());
        $metadata->addPropertyConstraint('username', new Email(array(
            'checkMX'   => true,
            'checkHost' => true,
        )));
    }

    public static function getAllGenders()
    {
        return array(
            self::GENDER_FEMALE => 'Female',
            self::GENDER_MALE   => 'Male'
        );
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $queries;

    /**
     * Constructor
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->queries = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add queries
     *
     * @param \Entities\Query $queries
     * @return User
     */
    public function addQuery(\Entities\Query $queries)
    {
        $this->queries[] = $queries;

        return $this;
    }

    /**
     * Remove queries
     *
     * @param \Entities\Query $queries
     */
    public function removeQuery(\Entities\Query $queries)
    {
        $this->queries->removeElement($queries);
    }

    /**
     * Get queries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQueries()
    {
        return $this->queries;
    }

    public function isAdmin()
    {
        return $this->getRole()->isAdmin();
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $settings;


    /**
     * Add settings
     *
     * @param \Entities\UserSetting $settings
     * @return User
     */
    public function addSetting(\Entities\UserSetting $settings)
    {
        $this->settings[] = $settings;

        return $this;
    }

    /**
     * Remove settings
     *
     * @param \Entities\UserSetting $settings
     */
    public function removeSetting(\Entities\UserSetting $settings)
    {
        $this->settings->removeElement($settings);
    }

    /**
     * Get settings
     *
     * @return array
     */
    public function getSettings()
    {
        $settings = \Entities\UserSetting::getDefaultSettings();
        if (!is_null($this->settings)) {
            foreach ($this->settings as $setting) {
                $settings[$setting->getCode()] = $setting->getValue();
            }
        }
        return $settings;
    }
    /**
     * @var \DateTime
     */
    private $login_at;

    /**
     * @var \DateTime
     */
    private $active_at;


    /**
     * Set login_at
     *
     * @param \DateTime $loginAt
     * @return User
     */
    public function setLoginAt($loginAt)
    {
        $this->login_at = $loginAt;

        return $this;
    }

    /**
     * Get login_at
     *
     * @return \DateTime 
     */
    public function getLoginAt()
    {
        return $this->login_at;
    }

    /**
     * Set active_at
     *
     * @param \DateTime $activeAt
     * @return User
     */
    public function setActiveAt($activeAt)
    {
        $this->active_at = $activeAt;

        return $this;
    }

    /**
     * Get active_at
     *
     * @return \DateTime 
     */
    public function getActiveAt()
    {
        return $this->active_at;
    }
    /**
     * @var \DateTime
     */
    private $logout_at;


    /**
     * Set logout_at
     *
     * @param \DateTime $logoutAt
     * @return User
     */
    public function setLogoutAt($logoutAt)
    {
        $this->logout_at = $logoutAt;

        return $this;
    }

    /**
     * Get logout_at
     *
     * @return \DateTime 
     */
    public function getLogoutAt()
    {
        return $this->logout_at;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $documents;


    /**
     * Add documents
     *
     * @param \Entities\Document $documents
     * @return User
     */
    public function addDocument(\Entities\Document $documents)
    {
        $this->documents[] = $documents;

        return $this;
    }

    /**
     * Remove documents
     *
     * @param \Entities\Document $documents
     */
    public function removeDocument(\Entities\Document $documents)
    {
        $this->documents->removeElement($documents);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocuments()
    {
        return $this->documents;
    }
}
