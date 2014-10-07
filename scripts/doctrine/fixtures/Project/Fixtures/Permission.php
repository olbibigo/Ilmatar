<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class Permission extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $em)
    {
        $role            = $em->getRepository('\Entities\Role')->findOneByCode(\Entities\Role::TECHNICAL_ADMIN_CODE);
        $functionalities = $em->getRepository('\Entities\Functionality')->findAll();
        foreach ($functionalities as $functionality) {
            $perm = new \Entities\Permission();
            $perm->setRole($role);
            $perm->setFunctionality($functionality);
            $perm->setWriteOn();
            $em->persist($perm);
        }
        
        $perm = new \Entities\Permission();
        $perm->setRole($em->getRepository('\Entities\Role')->findOneByCode('MARKETING'));
        $perm->setFunctionality($em->getRepository('\Entities\Functionality')->findOneByCode(\Entities\Functionality::DASHBOARD));
        $em->persist($perm);       
        
        
        $em->flush();
    }
    
    public function getDependencies()
    {
        return array('Project\Fixtures\Role', 'Project\Fixtures\Functionality');
    }
}