<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Ilmatar\HelperFactory;

class Mail extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $em)
    {
        $mailHelper = HelperFactory::build(
            'MailHelper',
            array(
                'mailer'             => null,
                'templateRepository' => $em->getRepository('\\Entities\\MailTemplate')
            ),
            array(
                'orm.em' => $em
            )
        );
        
        $mailHelper->createAsynchronousMessageFromTemplate(
            'RENEW_PASSWORD',
            'receiv@er.com'
        );
    }

    public function getDependencies()
    {
        return array('Project\Fixtures\MailTemplate');
    }
}