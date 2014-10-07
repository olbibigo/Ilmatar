<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class NumberHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'NumberHelper',
            array(
               //Nothing right now
            )
        );
    }
    /**
     * @group NumberHelperTest
     * @group NumberHelperTest::testFormatNumber
     */
    public function testFormatNumber()
    {
        $this->assertEquals('555666.77', $this->helper->formatNumber(555666.774));
        $this->assertEquals('555666.78', $this->helper->formatNumber(555666.777));
        $this->assertEquals('766.78', $this->helper->formatNumber('766.777 things'));
    }
}
