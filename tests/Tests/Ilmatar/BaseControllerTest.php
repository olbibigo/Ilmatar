<?php
namespace Tests\Ilmatar\Controller;

use Ilmatar\Tests\AbstractTestCase;

class BaseControllerTest extends AbstractTestCase
{
    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new \Project\Controller\PublicBackController($this->app);
    }
    
    public function providerIsTrue()
    {
        return array(
            array(
                array("a" => false, "b" => true),
                "c",
                false
            ),
            array(
                array("a" => false, "b" => true),
                "b",
                true
            ),
            array(
                array("a" => false, "b" => true),
                "a",
                false
            ),
            array(
                array("a" => "string", "b" => true),
                "a",
                false
            )
        );
    }
    /**
     * @group BaseControllerTest
     * @group BaseControllerTest::testIsTrue
     * @dataProvider providerIsTrue
     */
    public function testIsTrue($array, $value, $expected)
    {
        $this->assertEquals($expected, $this->controller->isTrue($array, $value));
    }
    
    /**
     * @group BaseControllerTest
     * @group BaseControllerTest::testToken
     */
    public function testToken()
    {
        $this->assertFalse($this->app['session']->has(\Ilmatar\BaseController::PARAM_TOKEN));
        $this->assertFalse($this->app['session']->has(\Ilmatar\BaseController::PARAM_TOKEN_BIRTH));
        $this->assertFalse(self::callMethod($this->controller, 'isValidToken', array('', $this->app)));
        $token = self::callMethod($this->controller, 'generateToken', array($this->app));
        $this->assertTrue($this->app['session']->has(\Ilmatar\BaseController::PARAM_TOKEN));
        $this->assertTrue($this->app['session']->has(\Ilmatar\BaseController::PARAM_TOKEN_BIRTH));
        $birth = $this->app['session']->get(\Ilmatar\BaseController::PARAM_TOKEN_BIRTH);
        $this->assertEquals($token, $this->app['session']->get(\Ilmatar\BaseController::PARAM_TOKEN));
        sleep(1);
        $this->assertTrue(self::callMethod($this->controller, 'isValidToken', array($token, $this->app)));
        $this->assertNotEquals($birth, $this->app['session']->get(\Ilmatar\BaseController::PARAM_TOKEN_BIRTH));
    }
}
