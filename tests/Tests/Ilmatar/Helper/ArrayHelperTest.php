<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class ArrayHelperTest extends AbstractTestCase
{
    protected $helper;

    protected $input = array(
        array(
            'id'          => 1,
            'name'        => 'nom 1',
            'description' => 'description 1',
        ),
        array(
            'id'          => 2,
            'name'        => 'nom 2',
            'description' => '";<xxx>;"',
        )
    );
    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'ArrayHelper',
            array(
               //Nothing right now
            )
        );
    }
    /**
     * @group ArrayHelperTest
     * @group ArrayHelperTest::getXmlFromArray
     */
    public function testGetXmlFromArray()
    {
        $expected = '<items><item><id><![CDATA[1]]></id><name><![CDATA[nom 1]]></name><description><![CDATA[description 1]]></description></item><item><id><![CDATA[2]]></id><name><![CDATA[nom 2]]></name><description><![CDATA[";;"]]></description></item></items>';
        $this->assertEquals($expected, $this->helper->getXmlFromArray($this->input, 'items', 'item'));
        $this->assertInstanceOf('\DOMDocument', $this->helper->getXmlFromArray($this->input, 'items', 'item', 'xml'));

        $expected = '<items><item><id><![CDATA[1]]></id><name><![CDATA[nom 1]]></name><description><![CDATA[description 1]]></description></item><item><id><![CDATA[2]]></id><name><![CDATA[nom 2]]></name><description><![CDATA[";<xxx>;"]]></description></item></items>';
        $this->assertEquals($expected, $this->helper->getXmlFromArray($this->input, 'items', 'item', 'string', false));
        $this->assertInstanceOf('\DOMDocument', $this->helper->getXmlFromArray($this->input, 'items', 'item', 'xml'));

        $this->assertEquals('<items/>', $this->helper->getXmlFromArray(array(), 'items', 'item'));
    }
    /**
     * @group ArrayHelperTest
     * @group ArrayHelperTest::testGetJsonFromArray
     */
    public function testGetJsonFromArray()
    {
        $expected = '[{"id":1,"name":"nom 1","description":"description 1"},{"id":2,"name":"nom 2","description":"\";<xxx>;\""}]';
        $this->assertEquals($expected, $this->helper->getJsonFromArray($this->input));

        $this->assertEquals('[]', $this->helper->getJsonFromArray(array()));
    }
    /**
     * @group ArrayHelperTest
     * @group ArrayHelperTest::testGetCsvFromArray
     */
    public function testGetCsvFromArray()
    {

        $expected = 'id;name;description'."\n".'1;nom 1;description 1'."\n".'2;nom 2;"\;\;"'."\n";
        $this->assertEquals($expected, $this->helper->getCsvFromArray($this->input));

        $this->assertEmpty($this->helper->getCsvFromArray(array()));
    }
    /**
     * @group ArrayHelperTest
     * @group ArrayHelperTest::testGetHTMLSelectFromArray
     */
    public function testGetHTMLSelectFromArray()
    {
        $expected = '<select class="myclass" id="myid"><option value="1">description 1</option><option value="2">&quot;;&lt;xxx&gt;;&quot;</option></select>';
        $this->assertEquals($expected, $this->helper->getHTMLSelectFromArray($this->input, 'id', 'description', array('class' => 'myclass', 'id' => 'myid')));

        $expected = '<select><option value="1">description 1</option><option value="2">&quot;;&lt;xxx&gt;;&quot;</option></select>';
        $this->assertEquals($expected, $this->helper->getHTMLSelectFromArray($this->input, 'id', 'description'));
        
        $expected = '<select></select>';
        $this->assertEquals('<select></select>', $this->helper->getHTMLSelectFromArray(array(), 'id', 'description'));
    }
    /**
     * @group ArrayHelperTest
     * @group ArrayHelperTest::testBuildAssociativeArray
     */
    public function testBuildAssociativeArray()
    {
        $assoc = $this->helper->buildAssociativeArray(
            [
                new TheClass('id1', 'nom1', 'description1'),
                new TheClass('id2', 'nom2', 'description2')
            ],
            'getId',
            'description'
        );
        $this->assertEquals(['id1' => 'description1', 'id2' => 'description2'], $assoc);
    }
}
