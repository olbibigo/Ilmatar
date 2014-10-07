<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class PdfHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'PdfHelper',
            ['app.root'=> $this->app['app.root']]
        );
    }
    /**
     * @group PdfHelperTest
     * @group PdfHelperTest::testPdfGeneration
     */
    public function testPdfGeneration()
    {
        $str = $this->helper->generatePdf(
            __DIR__ . '/../fixtures/pdfTemplate.htm',
            array('NameToReplace' => 'ReplacedName')
        );
        $this->assertStringStartsWith('%PDF-1.4', $str);
        $this->assertEquals(false, strpos($str, 'R e p l a c e d N a m e'));
    }
    /**
     * @group PdfHelperTest
     * @group PdfHelperTest::testMultiplePdfGeneration
     */
    public function testMultiplePdfGeneration()
    {
        $strs = $this->helper->generateMultiplePdf(
            __DIR__ . '/../fixtures/pdfTemplate.htm',
            array(
                array('NameToReplace' => 'ReplacedName0'),
                array('NameToReplace' => 'ReplacedName1'),
            ),
            array(
                $this->app['app.var'] . '/testPdf0.pdf',
                $this->app['app.var'] . '/testPdf1.pdf'
            )
        );
        $this->assertTrue(is_array($strs));
        foreach ($strs as $idx => $str) {
            $filename = $this->app['app.var'] . '/testPdf' . $idx . '.pdf';
            $this->assertTrue(file_exists($filename));
            $this->assertStringStartsWith('%PDF-1.4', $str);
            $this->assertEquals(false, strpos($str, 'R e p l a c e d N a m e ' . $idx));
            @unlink($filename);
        }
    }
}
