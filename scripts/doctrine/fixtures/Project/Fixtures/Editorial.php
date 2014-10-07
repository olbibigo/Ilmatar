<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class Editorial extends AbstractFixture /*implements DependentFixtureInterface*/
{
    public function load(ObjectManager $em)
    {
        $editorial = new \Entities\Editorial();
        $editorial->setCode('TEST_PUSH');
        $editorial->setDescription('A little description');
        $editorial->setBody('Bonjour %%user_name%%. ceci est un push');
        $em->persist($editorial);     
        $em->flush();
    }

    /*public function getDependencies()
    {
        return array();
    }*/
}