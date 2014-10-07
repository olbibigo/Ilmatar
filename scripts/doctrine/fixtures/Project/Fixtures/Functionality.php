<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class Functionality extends AbstractFixture /*implements DependentFixtureInterface*/
{
    public function load(ObjectManager $em)
    {
        $refl = new \ReflectionClass('\Entities\Functionality');
        $keys = $refl->getConstants();

        foreach ($keys as $key => $value) {
            $functionality = new \Entities\Functionality();
            $functionality->setCode($value);
            $em->persist($functionality);
        }
        $em->flush();
    }
   
    /*public function getDependencies()
    {
        return array();
    }*/
}