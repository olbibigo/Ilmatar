<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Carbon\Carbon;

class FlashMessage extends AbstractFixture
{
    public function load(ObjectManager $em)
    {
        $fm = new \Entities\FlashMessage(
            array(
                'target'   => \Entities\FlashMessage::TARGET_ALL,
                'subject'  => 'Subject',
                'body'     => 'This is a flash message body for all visitors',
                'begin_at' => Carbon::now()->subDays(1),
                'end_at'   => Carbon::now()->addDays(1)
            )
        );
        $em->persist($fm);

        $fm = new \Entities\FlashMessage(
            array(
                'target'   => \Entities\FlashMessage::TARGET_ONLY_USERS,
                'subject'  => 'Subject',
                'body'     => 'This is a flash message body for all connected users',
                'begin_at' => Carbon::now()->subDays(1),
                'end_at'   => Carbon::now()->addDays(1)
            )
        );
        $em->persist($fm);
        
        $em->flush();
    }
}