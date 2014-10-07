<?php
namespace Version20140101000000;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class Role extends AbstractFixture
{
    public function load(ObjectManager $em)
    {
        $role = new \Entities\Role();
        $role->setDescription('Technical administrator');
        $role->setCode(\Entities\Role::TECHNICAL_ADMIN_CODE);
        $em->persist($role);
        
        $role = new \Entities\Role();
        $role->setDescription('Functional administrator');
        $role->setCode(\Entities\Role::FUNCTIONAL_ADMIN_CODE);
        $em->persist($role);

        $role = new \Entities\Role();
        $role->setDescription('Basic role');
        $role->setCode(\Entities\Role::BASIC_CODE);
        $em->persist($role);

        $em->flush();
    }
}