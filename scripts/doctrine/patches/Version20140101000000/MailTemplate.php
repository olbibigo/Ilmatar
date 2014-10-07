<?php
namespace Version20140101000000;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class MailTemplate extends AbstractFixture
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
}
