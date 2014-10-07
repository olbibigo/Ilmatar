<?php
namespace Version20140101000000;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class Permission extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $em)
    {   
        $functionnalities = $em->getRepository('\Entities\Functionality')->findAll();
       
        $role = $em->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::TECHNICAL_ADMIN_CODE);
        foreach ($functionnalities as $functionality) {
            $perm = new \Entities\Permission();
            $perm->setRole($role);
            $perm->setFunctionality($functionality);
            $perm->setWriteOn();
            $em->persist($perm);
        }

        $role = $em->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::FUNCTIONAL_ADMIN_CODE);
        $excluded = [
            \Entities\Functionality::LOG,
            \Entities\Functionality::MAINTENANCE
        ];
        foreach ($functionnalities as $functionality) {
            if (!in_array($functionality->getCode(), $excluded)) {
                $perm = new \Entities\Permission();
                $perm->setRole($role);
                $perm->setFunctionality($functionality);
                $perm->setWriteOn();
                $em->persist($perm);
            }
        }

        $functionality  = $em->getRepository('\Entities\Functionality')->findOneByCode(\Entities\Functionality::DASHBOARD);
        $perm           = new \Entities\Permission();
        $perm->setRole($em->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::BASIC_CODE));
        $perm->setFunctionality($functionality);
        $perm->setReadOn();
        $em->persist($perm);
        
        $em->flush();
    }
    
    public function getDependencies()
    {
        return array('Version20140101000000\Role', 'Version20140101000000\Functionality');
    }
}