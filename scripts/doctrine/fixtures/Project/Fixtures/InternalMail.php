<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Carbon\Carbon;

class InternalMail extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $em)
    {
        $im = new \Entities\InternalMail(
            array(
                'subject' => 'Subject1',
                'body'    => 'Body1',
                'from'    => $em->find('\Entities\User', 2),
                'to'      => $em->find('\Entities\User', 1)
            )
        );
        $em->persist($im);

        $im = new \Entities\InternalMail(
            array(
                'subject' => 'Subject2',
                'body'    => 'Body2',
                'from'    => $em->find('\Entities\User', 3),
                'to'      => $em->find('\Entities\User', 1),
                'read_at' => Carbon::now()
            )
        );
        $em->persist($im);

        $im = new \Entities\InternalMail(
            array(
                'subject' => 'Subject3',
                'body'    => 'Body3',
                'from'    => $em->find('\Entities\User', 1),
                'to'      => $em->find('\Entities\User', 2),
            )
        );
        $em->persist($im);

        $im = new \Entities\InternalMail(
            array(
                'subject' => 'Subject4',
                'body'    => 'Body4',
                'from'    => $em->find('\Entities\User', 1),
                'to'      => $em->find('\Entities\User', 3),
                'read_at' => Carbon::now()
            )
        );
        $em->persist($im);
        
        $em->flush();
    }
    
    public function getDependencies()
    {
        return array('Project\Fixtures\User');
    }
}