<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Carbon\Carbon;

class Pipo extends AbstractFixture implements DependentFixtureInterface
{
    const NB_PIPOS      = 100;
    const CRYPTED_VALUE = 'Hello World!';
    
    public function load(ObjectManager $em)
    {
        $user = $em->getRepository('\Entities\User');
        $functionality = $em->getRepository('\Entities\Functionality');
        for ($i = 0;  $i < self::NB_PIPOS; ++$i) {
            $nuser = $user->findOneById(($i % 100) +1);
            $pipo = new \Entities\Pipo();
            $pipo->setValue(rand(0, 1000) / 100);
            $pipo->setUser($nuser); //rand(1, count($user->findAll()))));
            $pipo->setFunctionality($functionality->findOneById(rand(0, count($functionality->findAll()))));
            $pipo->setThetypeDate(Carbon::now()->subDays(rand(1, 1000)));
            $pipo->setThedatetimeAt(Carbon::now());
            $pipo->setEmail($nuser->getUsername());
            $pipo->setCrypto(self::CRYPTED_VALUE);
            $pipo->setMycheck(rand(0, 1));
            $em->persist($pipo);
        }
        $em->flush();
    }

    public function getDependencies()
    {
        return array('Project\Fixtures\User');
    }
}