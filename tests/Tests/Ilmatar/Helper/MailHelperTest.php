<?php
namespace Tests\Ilmatar\Helper;

use Ilmatar\HelperFactory;
use Ilmatar\Tests\AbstractTestCase;
use Ilmatar\TagManager;

class MailHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'MailHelper',
            array(
                'mailer'             => $this->app['mailer'],
                'templateRepository' => $this->app['orm.em']->getRepository('\\Entities\\MailTemplate')
            ),
            array( 'orm.em' => $this->app['orm.em'])
        );
    }
    /**
     * @group MailHelperTest
     * @group MailHelperTest::testCreateMessage
     */
    public function testCreateMessage()
    {
        $msg = $this->helper->createMessage(
            "subject",
            "body",
            array('send@er.com' => 'Send Er'),
            array('receiv@er.com' => 'Receiv Er')
        );
        $this->assertInstanceOf('\Swift_Message', $msg);
        $this->assertEquals("subject", $msg->getSubject());
        $this->assertEquals("body", $msg->getBody());
        $this->assertEquals(array('send@er.com' => 'Send Er'), $msg->getFrom());
        $this->assertEquals(array('receiv@er.com' => 'Receiv Er'), $msg->getTo());
        $this->assertEquals("text/html", $msg->getContentType());
        $this->assertEquals("utf-8", $msg->getCharset());
    }
    
    /**
     * @group MailHelperTest
     * @group MailHelperTest::testCreateMessageFromTemplate
     */
    public function testCreateMessageFromTemplate()
    {
        $tagManager = new TagManager(
            $this->app['app.tags.strategies'],
            array(
                'user'     => $this->app['orm.em']->find('\\Entities\\User', 1),
                'password' => 'xxx'
            )
        );
        $msg = $this->helper->createMessageFromTemplate(
            'RENEW_PASSWORD',
            $tagManager,
            array('send@er.com' => 'Send Er'),
            array('receiv@er.com' => 'Receiv Er')
        );
        $this->assertInstanceOf('\Swift_Message', $msg);
        $this->assertEquals("Ilmatar: Your new password", $msg->getSubject());
        $this->assertEquals("Bonjour A. Nistrateur,<br/>Votre nouveau mot de passe est xxx.", $msg->getBody());
        $this->assertEquals(array('send@er.com' => 'Send Er'), $msg->getFrom());
        $this->assertEquals(array('receiv@er.com' => 'Receiv Er'), $msg->getTo());
        $this->assertEquals("text/html", $msg->getContentType());
        $this->assertEquals("utf-8", $msg->getCharset());
    }
    /**
     * @group MailHelperTest
     * @group MailHelperTest::testCreateAsynchronousMessageFromTemplate
     */
    public function testCreateAsynchronousMessageFromTemplate()
    {
        $tagManager = new TagManager(
            $this->app['app.tags.strategies'],
            array(
                'user'     => $this->app['orm.em']->find('\\Entities\\User', 1),
                'password' => 'xxx'
            )
        );
        $msg = $this->helper->createAsynchronousMessageFromTemplate(
            'RENEW_PASSWORD',
            'receiv@er2.com',
            $tagManager
        );
        $this->assertInstanceOf('\Entities\Mail', $msg);
        $this->assertEquals("Ilmatar: Your new password", $msg->getObject());
        $this->assertEquals("Bonjour A. Nistrateur,<br/>Votre nouveau mot de passe est xxx.", $msg->getBody());
        $this->assertEquals('receiv@er2.com', $msg->getRecipient());
        $this->assertEquals(false, $msg->getIsError());
        $this->assertEquals(0, $msg->getAttemptCount());
        
        $msg->markAsSent();
        $this->app['orm.em']->persist($msg);
        $this->app['orm.em']->flush();
        
        $msg = $this->helper->createAsynchronousMessageFromTemplate(
            'RENEW_PASSWORD',
            'receiv@er2.com',
            $tagManager
        );
        $this->assertNull($msg);//Not allowed duplicate
        
        $msg = $this->helper->createAsynchronousMessageFromTemplate(
            'RENEW_PASSWORD',
            'receiv@er2.com',
            $tagManager,
            true//Allowed duplicate
        );
        $this->assertInstanceOf('\Entities\Mail', $msg);
    }
}
