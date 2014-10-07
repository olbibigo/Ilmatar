<?php
namespace Tests\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class Version extends AbstractFixture
{
    public function load(ObjectManager $em)
    {
        $version = new \Entities\Version();
        $version->setVersion(1234);
        $em->persist($version);
        $em->flush();
    }
}
