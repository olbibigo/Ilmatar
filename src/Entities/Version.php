<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;

/**
 * Version
 */
class Version extends BaseEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $version;


    /**
     * Get id
     *
     * @codeCoverageIgnore
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set version
     *
     * @codeCoverageIgnore
     *
     * @param string $version The version number
     *
     * @return Version
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @codeCoverageIgnore
     *
     * @return string 
     */
    public function getVersion()
    {
        return $this->version;
    }
}
