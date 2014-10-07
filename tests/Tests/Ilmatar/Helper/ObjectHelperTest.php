<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class ObjectHelperTest extends AbstractTestCase
{
    protected $helper;

    protected $input = array();
    
    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'ObjectHelper',
            array(
               //Nothing right now
            )
        );
        $this->input[] = new TheClass(1, 'nom 1', 'description 1');
        $this->input[] = new TheClass(2, 'nom 2', '";<xxx>;"');
    }
    /**
     * @group ArrayHelperTest
     * @group ArrayHelperTest::getXmlFromObjects
     */
    public function testGetXmlFromObjects()
    {
        $expected = '<items><item><id><![CDATA[1]]></id><name><![CDATA[nom 1]]></name><description><![CDATA[description 1]]></description></item><item><id><![CDATA[2]]></id><name><![CDATA[nom 2]]></name><description><![CDATA[";;"]]></description></item></items>';
        $this->assertEquals($expected, $this->helper->getXmlFromObjects($this->input, array('id', 'name', 'description'), 'items', 'item'));
        $this->assertInstanceOf('\DOMDocument', $this->helper->getXmlFromObjects($this->input, array('id', 'name', 'description'), 'items', 'item', 'xml'));
        
        $this->assertEquals('<items/>', $this->helper->getXmlFromObjects(array(), array('id', 'name', 'description'), 'items', 'item'));
    }
    /**
     * @group ArrayHelperTest
     * @group ArrayHelperTest::testGetJsonFromObjects
     */
    public function testGetJsonFromObjects()
    {
        $expected = '[{"id":1,"name":"nom 1","description":"description 1"},{"id":2,"name":"nom 2","description":"\";<xxx>;\""}]';
        $this->assertEquals($expected, $this->helper->getJsonFromObjects($this->input, array('id', 'name', 'description')));
        
        $this->assertEquals('[]', $this->helper->getJsonFromObjects(array(), array('id', 'name', 'description')));
    }
    /**
     * @group ArrayHelperTest
     * @group ArrayHelperTest::testGetCsvFromObjects
     */
    public function testGetCsvFromObjects()
    {

        $expected = 'id;name;description'."\n".'1;nom 1;description 1'."\n".'2;nom 2;"\;\;"'."\n";
        $this->assertEquals($expected, $this->helper->getCsvFromObjects($this->input, array('id', 'name', 'description')));
        
        $this->assertEquals('', $this->helper->getCsvFromObjects(array(), array('id', 'name', 'description')));
    }
    /**
     * @group ArrayHelperTest
     * @group ArrayHelperTest::testGetHTMLSelectFromObjects
     */
    public function testGetHTMLSelectFromObjects()
    {
        $expected = '<select class="myclass" id="myid"><option value="">---</option><option value="1">description 1</option><option value="2">&quot;;&lt;xxx&gt;;&quot;</option></select>';
        $this->assertEquals($expected, $this->helper->getHTMLSelectFromObjects($this->input, array('id', 'name', 'description'), 'id', 'description', true, array('class' => 'myclass', 'id' => 'myid')));
        
        $expected = '<select><option value="1">description 1</option><option value="2">&quot;;&lt;xxx&gt;;&quot;</option></select>';
        $this->assertEquals($expected, $this->helper->getHTMLSelectFromObjects($this->input, array('id', 'name', 'description'), 'id', 'description'));
        $expected = '<select></select>';
        
        $this->assertEquals('<select></select>', $this->helper->getHTMLSelectFromObjects(array(), array('id', 'name', 'description'), 'id', 'description'));
    }
}
