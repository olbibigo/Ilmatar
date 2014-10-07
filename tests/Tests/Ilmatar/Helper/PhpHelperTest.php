<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class PhpHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'PhpHelper',
            array(
               //Nothing right now
            )
        );
    }
    /**
     * @group PhpHelperTest
     * @group PhpHelperTest::testSetConfiguration
     */
    public function testSetConfiguration()
    {
        $oldValue = intval(ini_get('precision'));
        $newValue = $oldValue - 1;
        $this->helper->setConfiguration('precision', sprintf("%s", $newValue));
        $this->assertEquals(sprintf("%s", $newValue), ini_get('precision'));

        $this->assertEquals("0", ini_get('display_errors'));
        $this->helper->setConfiguration('display_errors', true);
        $this->assertEquals("1", ini_get('display_errors'));
        $this->helper->setConfiguration('display_errors', false);
        $this->assertEquals("0", ini_get('display_errors'));
    }
    
    /**
     * @group PhpHelperTest
     * @group PhpHelperTest::testResetConfiguration
     */
    public function testResetConfiguration()
    {
        $oldValue = ini_get('precision');
        $this->helper->setConfiguration('precision', "10");
        $this->assertNotEquals($oldValue, ini_get('precision'));
        $this->helper->resetConfiguration('precision');
        $this->assertEquals($oldValue, ini_get('precision'));
    }
}
