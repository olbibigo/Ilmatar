<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;
use Ilmatar\Exception\TranslatedException;

/**
 * Job
 */
class Job extends BaseEntity
{
    const JOB_STATUS_READY   = 0;
    const JOB_STATUS_RUNNING = 1;
    const JOB_STATUS_ERROR   = 2;
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var boolean
     */
    private $is_active = true;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $schedule;

    /**
     * @var integer
     */
    private $run_counter = 0;

    /**
     * @var integer
     */
    private $run_time = 0;

    /**
     * @var \DateTime
     */
    private $finished_at;

    /**
     * @var integer
     */
    private $status = self::JOB_STATUS_READY;

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
     * Set code
     *
     * @param string $code
     * @return Job
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
     * Set is_active
     *
     * @param boolean $isActive
     * @return Job
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
     * Set description
     *
     * @param string $description
     * @return Job
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
     * Set class
     *
     * @param string $class
     * @return Job
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set schedule
     *
     * @param string $schedule
     * @return Job
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule
     *
     * @return string 
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Set run_counter
     *
     * @param integer $runCounter
     * @return Job
     */
    public function setRunCounter($runCounter)
    {
        $this->run_counter = $runCounter;

        return $this;
    }

    /**
     * Get run_counter
     *
     * @return integer 
     */
    public function getRunCounter()
    {
        return $this->run_counter;
    }

    /**
     * Set run_time
     *
     * @param integer $runTime
     * @return Job
     */
    public function setRunTime($runTime)
    {
        $this->run_time = $runTime;

        return $this;
    }

    /**
     * Get run_time
     *
     * @return integer 
     */
    public function getRunTime()
    {
        return $this->run_time;
    }

    /**
     * Set finished_at
     *
     * @param \DateTime $finishedAt
     * @return Job
     */
    public function setFinishedAt($finishedAt)
    {
        $this->finished_at = $finishedAt;

        return $this;
    }

    /**
     * Get finished_at
     *
     * @return \DateTime 
     */
    public function getFinishedAt()
    {
        return $this->finished_at;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Job
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Job
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
     * @return Job
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
     * @return Job
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
     * @return Job
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
   
    public static function getAllStatus()
    {
        return array(
            self::JOB_STATUS_READY   => 'Ready',
            self::JOB_STATUS_RUNNING => 'Running',
            self::JOB_STATUS_ERROR   => 'Error'
        );
    }

    /**
     * @ORM\PrePersist
     */
    public function assertValidJob()
    {
        if (!in_array($this->status, array_keys(self::getAllStatus()))) {
            throw new TranslatedException('The field "%s" must be valid.', array('trans:Status'));
        }
    }
}
