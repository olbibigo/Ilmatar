<?php

namespace Entities;

use Ilmatar\BaseEntity;

/**
 * IpBlacklist
 */
class IpBlacklist extends BaseEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var \DateTime
     */
    private $until_date;


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
     * Set ip
     *
     * @param string $ip
     * @return IpBlacklist
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set until_date
     *
     * @param \DateTime $untilDate
     * @return IpBlacklist
     */
    public function setUntilDate($untilDate)
    {
        $this->until_date = $untilDate;

        return $this;
    }

    /**
     * Get until_date
     *
     * @return \DateTime 
     */
    public function getUntilDate()
    {
        return $this->until_date;
    }
}
