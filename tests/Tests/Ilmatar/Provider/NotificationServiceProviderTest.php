<?php
namespace Tests\Ilmatar\Provider;

use Ilmatar\Tests\AbstractTestCase;

class NotificationServiceProviderTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->app['session']->getFlashBag()->clear();
    }

    /**
     * @group NotificationServiceProviderTest
     * @group NotificationServiceProviderTest::testFlashMessageModeNone
     */
    public function testFlashMessageModeNone()
    {
        $this->assertTrue(isset($this->app["notification"]));
        $this->assertEmpty($this->app['session']->getFlashBag()->all());
        $this->app["notification"]('FIRST WARNING', 'warning', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_NONE);
        $this->app["notification"]('SECOND WARNING', 'warning', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_NONE);
        $fb = $this->app['session']->getFlashBag()->all();
        $this->assertCount(1, array_keys($fb));
        $this->assertCount(2, $fb['warning']);
        $this->assertEquals('FIRST WARNING', $fb['warning'][0]);
        $this->assertEquals('SECOND WARNING', $fb['warning'][1]);
    }

    /**
     * @group NotificationServiceProviderTest
     * @group NotificationServiceProviderTest::testFlashMessageModeOnlyType
     */
    public function testFlashMessageModeOnlyType()
    {
        $this->app["notification"]('FIRST INFO', 'info', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_NONE);
        $this->app["notification"]('FIRST WARNING', 'warning', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_ONLY_TYPE);
        $this->app["notification"]('SECOND WARNING', 'warning', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_ONLY_TYPE);
        $fb = $this->app['session']->getFlashBag()->all();
        $this->assertCount(2, array_keys($fb));
        $this->assertCount(1, $fb['warning']);
        $this->assertEquals('SECOND WARNING', $fb['warning'][0]);
    }

    /**
     * @group NotificationServiceProviderTest
     * @group NotificationServiceProviderTest::testFlashMessageModeOnlyLessPriority
     */
    public function testFlashMessageModeOnlyLessPriority()
    {
        $this->app["notification"]('FIRST INFO', 'info', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_NONE);
        $this->app["notification"]('FIRST EMERGENCY', 'emergency', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_NONE);
        $this->app["notification"]('FIRST WARNING', 'warning', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_ONLY_LESS_PRIORITY);
        $fb = $this->app['session']->getFlashBag()->all();
        $this->assertCount(2, array_keys($fb));
        $this->assertCount(1, $fb['warning']);
        $this->assertCount(1, $fb['emergency']);
        $this->assertEquals('FIRST EMERGENCY', $fb['emergency'][0]);
        $this->assertEquals('FIRST WARNING', $fb['warning'][0]);
    }

    /**
     * @group NotificationServiceProviderTest
     * @group NotificationServiceProviderTest::testFlashMessageModeAll
     */
    public function testFlashMessageModeAll()
    {
        $this->app["notification"]('FIRST INFO', 'info', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_NONE);
        $this->app["notification"]('FIRST EMERGENCY', 'emergency', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_NONE);
        $this->app["notification"]('FIRST WARNING', 'warning', \Ilmatar\Provider\NotificationServiceProvider::RESET_MODE_ALL);
        $fb = $this->app['session']->getFlashBag()->all();
        $this->assertCount(1, array_keys($fb));
        $this->assertEquals('FIRST WARNING', $fb['warning'][0]);
    }

    /**
     * @group NotificationServiceProviderTest
     * @group NotificationServiceProviderTest::testScheduledFlashMessageOnlyUser

     */
    public function testScheduledFlashMessageOnlyUser()
    {
        $this->assertTrue(isset($this->app["scheduled_notification"]));
        $this->app["scheduled_notification"](\Entities\FlashMessage::TARGET_ONLY_USERS);
        $fb = $this->app['session']->getFlashBag()->all();
        $this->assertCount(1, array_keys($fb));
        $this->assertEquals('<b>Subject</b><br/><br/><div style="text-align:left">This is a flash message body for all connected users</div>', $fb['notice'][0]);
    }
    /**
     * @group NotificationServiceProviderTest
     * @group NotificationServiceProviderTest::testScheduledFlashMessageAllVisitors

     */
    public function testScheduledFlashMessageAllVisitors()
    {
        $this->app["scheduled_notification"](\Entities\FlashMessage::TARGET_ALL);
        $fb = $this->app['session']->getFlashBag()->all();
        $this->assertCount(1, array_keys($fb));
        $this->assertEquals('<b>Subject</b><br/><br/><div style="text-align:left">This is a flash message body for all visitors</div>', $fb['notice'][0]);
    }
}
