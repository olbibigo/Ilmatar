<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class HttpHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'HttpHelper',
            array(
                'validator'=> $this->app['validator']
            )
        );
    }
    /**
     * @group HttpHelperTest
     * @group HttpHelperTest::testSendRequest
     */
    public function testSendRequest()
    {
        //Wold be better: http://docs.guzzlephp.org/en/latest/testing/unit-testing.html#phpunit-integration
        $str = $this->helper->sendRequest(
            "http://www.w3.org/"
        );

        $this->assertTrue(is_string($str) && !empty($str));
        $dom = new \DOMDocument();
        $dom->loadHTML($str);
        $list = $dom->getElementsByTagName("title");
        $this->assertTrue($list->length > 0);
        $title = $list->item(0)->textContent;
        $this->assertEquals('World Wide Web Consortium (W3C)', $title);
    }
}
