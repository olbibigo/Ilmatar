<?php
namespace Version19700103000000;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class Version extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $em)
    {
        $version = new \Entities\Version();
        $version->setVersion('123');
        $em->persist($version);
        $em->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
