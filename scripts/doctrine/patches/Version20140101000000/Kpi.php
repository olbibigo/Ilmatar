<?php
namespace Version20140101000000;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Carbon\Carbon;

class Kpi extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $em)
    {
        $kpi = new \Entities\Kpi();
        $kpi->setCode('KPI_NB_USERS');
        $kpi->setDescription('Number of active user accounts');
        $kpi->setClass('NbUsersKpi');
        $kpi->addRole($em->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::TECHNICAL_ADMIN_CODE));//Kpi is the owning side. See Entities\Kpi:addRole()
        $kpi->addRole($em->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::FUNCTIONAL_ADMIN_CODE));//Kpi is the owning side. See Entities\Kpi:addRole()
        $em->persist($kpi);
        
        for ($i = 1; $i < 365; ++$i) {
            $value = new \Entities\KpiValue();
            $value->setKpi($kpi);
            $value->setDate(Carbon::now()->subDays($i));
            $value->setValue(rand(0, 100));
            
            $em->persist($value);
        }
        
        $kpi = new \Entities\Kpi();
        $kpi->setCode('KPI_NB_USERS2');
        $kpi->setDescription('Number of active user accounts 2');
        $kpi->setClass('NbUsersKpi');
        $kpi->addRole($em->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::TECHNICAL_ADMIN_CODE));//Kpi is the owning side. See Entities\Kpi:addRole()
        $kpi->addRole($em->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::FUNCTIONAL_ADMIN_CODE));//Kpi is the owning side. See Entities\Kpi:addRole()
        $em->persist($kpi);
        
        for ($i = 1; $i < 100; ++$i) {
            $value = new \Entities\KpiValue();
            $value->setKpi($kpi);
            $value->setDate(Carbon::now()->subDays($i));
            $value->setValue(rand(0, 100));
            
            $em->persist($value);
        }
        
        $em->flush();
    }

    public function getDependencies()
    {
        return array('Version20140101000000\Role');
    }
}