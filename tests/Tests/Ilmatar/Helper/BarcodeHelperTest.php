<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;
use Ilmatar\Twig\Extensions\ImgBase64Extension;

class BarcodeHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'BarcodeHelper',
            array(
               //Nothing right now
            )
        );
    }
    /**
     * @group BarcodeHelperTest
     * @group BarcodeHelperTest::testCreate1DPngBarcode
     */
    public function testCreate1DPngBarcode()
    {
        $img = $this->helper->create1DPngBarcode('123451234512', \Ilmatar\Helper\BarcodeHelper::BARECODE_1D_EAN13);
        $this->assertEquals(ImgBase64Extension::MIME_PNG, $img['mime']);
        $res = imagecreatefromstring($img['binary']);
        $this->assertFalse(false === $res);
        $this->assertEquals(230, imagesx($res));
        $this->assertEquals(65, imagesy($res));
        $this->assertTrue(imageistruecolor($res));
    }
    
    /**
     * @group BarcodeHelperTest
     * @group BarcodeHelperTest::testCreate2DPngBarcode
     */
    public function testCreate2DPngBarcode()
    {
        $img = $this->helper->create2DPngBarcode('http://www.helloworld.com');
        $this->assertEquals(ImgBase64Extension::MIME_PNG, $img['mime']);
        $res = imagecreatefromstring(base64_decode($img['binary']));
        $this->assertFalse(false === $res);
        $this->assertEquals(87, imagesx($res));
        $this->assertEquals(87, imagesy($res));
        $this->assertEquals(2, imagecolorstotal($res));
    }
}
