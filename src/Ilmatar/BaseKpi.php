<?php
namespace Ilmatar;

use Symfony\Bridge\Monolog\Logger;
use Doctrine\ORM\EntityManager;
use Carbon\Carbon;

abstract class BaseKpi
{
    protected $date;
    protected $kpi;
    protected $em;
    protected $repo;
    
    abstract public function compute();
    
    /**
     * Constructeur
     * 
     * @param array $options
     */
    public function __construct(Carbon $date, \Entities\Kpi $kpi, EntityManager $em, Logger $logger)
    {
        $this->date   = $date;
        $this->kpi    = $kpi;
        $this->em     = $em;
        $this->repo   = $em->getRepository('\\Entities\\KpiValue');
        $this->logger = $logger;
    }
    /**
     * Met à jour la BD.
     */
    public function insert()
    {
        $kpiVal = $this->repo->findOneBy(
            [
                'date' => $this->date,
                'kpi'  => $this->kpi
            ]
        );
        if (!($kpiVal instanceof \Entities\KpiValue)) {
            $kpiVal = new \Entities\KpiValue();
        }
        $value = floatval($this->compute());
        $kpiVal->setKpi($this->kpi);
        $kpiVal->setDate($this->date);
        $kpiVal->setValue($value);
        $this->em->persist($kpiVal);
        $this->em->flush();
        $this->logger->info(sprintf('Value for KPI %s today: %s', $this->kpi->getCode(), $value));
    }
}
