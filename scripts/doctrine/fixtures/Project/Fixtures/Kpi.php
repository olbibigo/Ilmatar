<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Carbon\Carbon;

class Kpi extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $em)
    {        
        $this->loadKpi($em, 'KPI_NB_USERS', 1);
        $this->loadKpi($em, 'KPI_NB_USERS2', 2);
        $em->flush();
    }

    public function getDependencies()
    {
        return array('Project\Fixtures\Role');
    }
    
    protected function loadKpi(ObjectManager $em, $code, $value)
    {
        $kpi = new \Entities\Kpi();
        $kpi->setCode($code);
        $kpi->setDescription('...');
        $kpi->setClass('NbUsersKpi');
        $kpi->addRole($em->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::TECHNICAL_ADMIN_CODE));
        $em->persist($kpi);

        for ($i = 0; $i < 365; ++$i) {//Add 1 year of data
            $kpiValue = new \Entities\KpiValue();
            $kpiValue->setKpi($kpi);
            $kpiValue->setDate(Carbon::createFromDate(2013, 1, 1)->addDays($i));
            $kpiValue->setValue($value);
            
            $em->persist($kpiValue);
        }
    }
}