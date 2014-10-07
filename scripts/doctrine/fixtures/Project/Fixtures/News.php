<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Carbon\Carbon;

class News extends AbstractFixture
{
    public function load(ObjectManager $em)
    {
        $fm = new \Entities\News(
            array(
                'subject'  => 'Subject',
                'body'     => 'This is a <b>body</b> for all visitors'
            )
        );
        $em->persist($fm);
        
        $em->flush();
    }
}