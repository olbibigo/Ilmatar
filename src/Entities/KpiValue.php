<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;

/**
 * KpiValue
 */
class KpiValue extends BaseEntity
{
    const VIEW_DAY   = 0;
    const VIEW_WEEK  = 1;
    const VIEW_MONTH = 2;
    const VIEW_YEAR  = 3;
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var float
     */
    private $value;
    
    /**
     * @var \Entities\Kpi
     */
    private $kpi;
    
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
     * Set date
     *
     * @param \DateTime $date
     * @return KpiValue
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set value
     *
     * @param float $value
     * @return KpiValue
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
     * Set kpi
     *
     * @param \Entities\Kpi $kpi
     * @return KpiValue
     */
    public function setKpi(\Entities\Kpi $kpi)
    {
        $this->kpi = $kpi;

        return $this;
    }

    /**
     * Get kpi
     *
     * @return \Entities\Kpi 
     */
    public function getKpi()
    {
        return $this->kpi;
    }
    
    public function getDataToDisplay($format = 'Y-m-d')
    {
        return [
            $this->date->format($format),
            $this->value
        ];
    }
    
    public static function getAllViews()
    {
        return [
            self::VIEW_DAY   => "Day",
            self::VIEW_WEEK  => "Week",
            self::VIEW_MONTH => "Month",
            self::VIEW_YEAR  => "Year"
        ];
    }
}
