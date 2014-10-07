<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MailTemplate extends AbstractFixture /*implements DependentFixtureInterface*/
{
    public function load(ObjectManager $em)
    {
        $mail = new \Entities\MailTemplate();
        $mail->setCode('RENEW_PASSWORD');
        $mail->setObject('Ilmatar: Your new password');
        $mail->setBody('Bonjour %%user_name%%,<br/>Votre nouveau mot de passe est %%user_password%%.');
        $em->persist($mail);     
        $em->flush();
    }

    /*public function getDependencies()
    {
        return array();
    }*/
}