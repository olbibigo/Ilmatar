<?php

namespace Entities;

use Ilmatar\BaseEntity;

/**
 * RouteAnalytics
 */
class RouteAnalytics extends BaseEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $page;

    /**
     * @var integer
     */
    private $counter;

    /**
     * @var \DateTime
     */
    private $active_at;

    /**
     * @var \Entities\User
     */
    private $user;


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
     * Set page
     *
     * @param string $page
     * @return RouteAnalytics
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return string 
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set counter
     *
     * @param integer $counter
     * @return RouteAnalytics
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get counter
     *
     * @return integer 
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * Set active_at
     *
     * @param \DateTime $activeAt
     * @return RouteAnalytics
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
     * Set user
     *
     * @param \Entities\User $user
     * @return RouteAnalytics
     */
    public function setUser(\Entities\User $user = null)
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
    
    public function incrementCounter()
    {
        $this->counter += 1;
    }
}
