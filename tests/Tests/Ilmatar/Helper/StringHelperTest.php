<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class StringHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'StringHelper',
            array(
               //Nothing right now
            ),
            array(
                'seedFile' => $this->app['app.var'] . '/seedFile.txt'
            )
        );
    }
    /**
     * @group StringHelperTest
     * @group StringHelperTest::testSnakeToCamel
     */
    public function testSnakeToCamel()
    {
        $this->assertEquals('ALittleString', $this->helper->snakeToCamel('a_little_string'));
        $this->assertEquals('aLittleString', $this->helper->snakeToCamel('a_little_string', true));
        $this->assertEquals('Alittlestring', $this->helper->snakeToCamel('ALittleString'));
        $this->assertEquals('Alittlestring', $this->helper->snakeToCamel('alittlestring'));
        $this->assertEquals('alittlestring', $this->helper->snakeToCamel('ALITTLESTRING', true));
        $this->assertEquals('alittlestring', $this->helper->snakeToCamel('ALittleString', true));
    }
    /**
     * @group StringHelperTest
     * @group StringHelperTest::testCamelToSnake
     */
    public function testCamelToSnake()
    {
        $this->assertEquals('a_little_string', $this->helper->camelToSnake('ALittleString'));
        $this->assertEquals('a_little_string', $this->helper->camelToSnake('aLittleString'));
        $this->assertEquals('alittlestring', $this->helper->camelToSnake('alittlestring'));
        $this->assertEquals('a_s_t_r_i_n_g', $this->helper->camelToSnake('ASTRING'));
        $this->assertEquals('a_little_string', $this->helper->camelToSnake('a_little_string'));
    }
    /**
     * @group StringHelperTest
     * @group StringHelperTest::testRemoveAccents
     */
    public function testRemoveAccents()
    {
        $this->assertEquals('oeaeAeAOiU', $this->helper->removeAccents('œæÆÀỒïÜ'));
    }
    /**
     * @group StringHelperTest
     * @group StringHelperTest::testGetClassFromString
     */
    public function testGetClassFromString()
    {
        $this->assertInstanceOf(
            '\Symfony\Component\HttpFoundation\Request',
            $this->helper->getClassFromString('\Symfony\Component\HttpFoundation\Request')
        );
        $this->assertNull($this->helper->getClassFromString('\MyDummyClass'));
        $this->assertNull($this->helper->getClassFromString('\vendor\pimple\pimple\lib\Pimple'));
        $this->assertInstanceOf(
            '\Pimple',
            $this->helper->getClassFromString('\vendor\pimple\pimple\lib\Pimple', $this->app['app.root'])
        );
    }
    /**
     * @group StringHelperTest
     * @group StringHelperTest::testCaseConversion
     */
    public function testCaseConversion()
    {
        $this->assertEquals('ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞŸÆŒÐØ', $this->helper->toUpperCase('äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø'));
        $this->assertEquals('äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø', $this->helper->toLowerCase('ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞŸÆŒÐØ'));
    }
    /**
     * @group StringHelperTest
     * @group StringHelperTest::testCompress
     */
    public function testCompress()
    {
        $str = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
        
        $compressedStr = $this->helper->compress($str);
        $this->assertLessThan(strlen($str), strlen($compressedStr));
        $this->assertEquals($str, $this->helper->uncompress($compressedStr));
        
        $compressedStr = $this->helper->compress($str, true);
        $this->assertLessThan(strlen($str), strlen($compressedStr));
        $this->assertEquals($str, $this->helper->uncompress($compressedStr, true));
    }
}
