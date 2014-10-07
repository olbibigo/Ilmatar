<?php
namespace Tests\Ilmatar\Application;

use Ilmatar\Application;
use Ilmatar\Tests\AbstractTestCase;

class ApplicationTest extends AbstractTestCase
{
    /**
     * Ilmatar\Application constructor test
     *
     * @param array  $values       Values for constructor
     * @param string $expectedName The name expected
     *
     * @return void
     *
     * @group ApplicationTest
     * @group ApplicationTest::testConstuct
     */
    public function testConstuct()
    {
        $app = new Application(array('name' => 'foo'));
        $this->assertEquals($app['name'], 'foo');
        Application::removeInstance('foo');
    }

    /**
     * Ilmatar\Application::getInstance test
     *
     * @return void
     *
     * @group ApplicationTest
     * @group ApplicationTest::testGetInstance
     */
    public function testGetInstance()
    {
        $this->assertNull(Application::getInstance('foo'));
        $app = new Application(array('name' => 'foo'));
        $this->assertEquals($app, Application::getInstance('foo'));
        Application::removeInstance('foo');
        $this->assertNull(Application::getInstance('foo'));
    }
}
