<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class Role extends AbstractFixture /*implements DependentFixtureInterface*/
{
    public function load(ObjectManager $em)
    {
        $role = new \Entities\Role();
        $role->setDescription('Technical administrator');
        $role->setCode(\Entities\Role::TECHNICAL_ADMIN_CODE);
        $em->persist($role);
        
        $role = new \Entities\Role();
        $role->setDescription('Marketing');
        $role->setCode('MARKETING');
        $em->persist($role);
        
        $role = new \Entities\Role();
        $role->setDescription('Support');
        $role->setCode('SUPPORT');
        $em->persist($role);
        
        $role = new \Entities\Role();
        $role->setDescription('SI');
        $role->setCode('SI');
        $em->persist($role);
        
        $em->flush();
    }

    /*public function getDependencies()
    {
        return array();
    }*/
}