<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class FileSystemHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'FileSystemHelper',
            array(
               //Nothing right now
            )
        );
        
        $root  = $this->app['app.var'] . '/rscandir';
        @mkdir($root);
        @file_put_contents($root . '/file1.txt', '');
        @file_put_contents($root . '/file2.php', '');
        @mkdir($root . '/subdir');
        @file_put_contents($root . '/subdir/file3.txt', '');
        @file_put_contents($root . '/subdir/file4.php', '');
        @file_put_contents($root . '/subdir/file5.php', '');
        @mkdir($root . '/subdir2');
        @file_put_contents($root . '/subdir2/subfile.php', '');
    }
    
    public function tearDown()
    {
        parent::tearDown();
        $this->helper->rrmdir($this->app['app.var'] . '/rscandir');
    }
    
    /**
     * @group FileSystemHelperTest
     * @group FileSystemHelperTest::testRrmdir
     */
    public function testRrmdir()
    {
        $root  = $this->app['app.var'] . '/test';
        @mkdir($root);
        @file_put_contents($root . '/file1', '');
        @mkdir($root . '/subdir');
        @file_put_contents($root . '/subdir/file2', '');
        
        $this->assertFileExists($root);
        $count = $this->helper->rrmdir($root);
        $this->assertFileNotExists($root);
    }
    /**
     * @group FileSystemHelperTest
     * @group FileSystemHelperTest::testGetFileInfos
     */
    public function testGetFileInfos()
    {
        $infos = $this->helper->getFileInfos(__FILE__);
        $this->assertInternalType('array', $infos);
        $this->assertEquals(__DIR__, $infos['dirname']);
        $this->assertEquals('text/html', $infos['mime']);
        
        $infos = $this->helper->getFileInfos(
            __FILE__,
            array(
                \Ilmatar\Helper\FileSystemHelper::FILE_FILENAME,
                \Ilmatar\Helper\FileSystemHelper::FILE_MIME
            )
        );
        $this->assertInternalType('array', $infos);
        $this->assertCount(2, $infos);
        $this->assertEquals('FileSystemHelperTest', $infos[\Ilmatar\Helper\FileSystemHelper::FILE_FILENAME]);
        $this->assertEquals('text/html', $infos[\Ilmatar\Helper\FileSystemHelper::FILE_MIME]);
        
        $this->assertEquals('text/html', $this->helper->getMimeType(__FILE__));
    }
    /**
     * @group FileSystemHelperTest
     * @group FileSystemHelperTest::testRscandir
     */
    public function testRscandir()
    {
        $files1 = $this->helper->rscandir($this->app['app.var'] . '/rscandir');
        $this->assertCount(4, $files1);
        $files2 = $this->helper->rscandir($this->app['app.var'] . '/rscandir', false, true, false);
        $this->assertCount(2, $files2);
        $files3 = $this->helper->rscandir($this->app['app.var'] . '/rscandir/sub*', true, false, false);
        $this->assertCount(2, $files3);
        $files4 = $this->helper->rscandir($this->app['app.var'] . '/rscandir/sub*', true, true, false);
        $this->assertCount(0, $files4);
        $files5 = $this->helper->rscandir($this->app['app.var'] . '/rscandir', false, false, true);
        $this->assertEquals(8, count($files5, COUNT_RECURSIVE));
        $files6 = $this->helper->rscandir($this->app['app.var'] . '/rscandir/*.php', true, false, true);
        $this->assertCount(4, $files6);
        $files7 = $this->helper->rscandir($this->app['app.var'] . '/rscandir/sub*', true, true, true);
        $this->assertCount(1, $files7);
        //idem files 5
        $files8 = $this->helper->rscandir($this->app['app.var'] . '/rscandir', false, true, true);
        $this->assertEquals(8, count($files5, COUNT_RECURSIVE));
    }
    /**
     * @group FileSystemHelperTest
     * @group FileSystemHelperTest::testGetFileType
     */
    public function testGetFileType()
    {
        $img = base64_decode(HelperFactory::build('BarcodeHelper')->create2DPngBarcode('HELLO WORLD!')['binary']);
        $this->assertTrue($this->helper->isFilePng($img));
        $this->assertFalse($this->helper->isFilePdf($img));
        
        $pdfHelper = HelperFactory::build(
            'PdfHelper',
            ['app.root'=> $this->app['app.root']]
        );
        $doc = $pdfHelper->generatePdf('');
        $this->assertFalse($this->helper->isFilePng($doc));
        $this->assertTrue($this->helper->isFilePdf($doc));
    }
}
