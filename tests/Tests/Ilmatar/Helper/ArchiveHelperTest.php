<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class ArchiveHelperTest extends AbstractTestCase
{
    protected $helper;
    protected $files = [];
    
    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'ArchiveHelper',
            ['app.var' => $this->app['app.var']]
        );

        for ($i = 0; $i < 10; $i++) {
            $filename = sprintf('tempfile%s.txt', $i);
            $filepath = $this->app['app.var'] . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($filepath, str_pad('', $i, "_"));
            $this->files[$filename] = $filepath;
        }
    }
    /**
     * @group ArchiveHelperTest
     * @group ArchiveHelperTest::testCreateArchive
     */
    public function testCreateArchive()
    {
        $archiveStr = $this->helper->create($this->files);

        $zippath    = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'archive.zip';
        file_put_contents($zippath, $archiveStr);
        
        $zip = new \ZipArchive();
        $zip->open($zippath);
        $this->assertEquals(10, $zip->numFiles);
        for ($i=0; $i < $zip->numFiles; $i++) {
            $stats = $zip->statIndex($i);
            $this->assertEquals(sprintf('tempfile%s.txt', $i), $stats['name']);
            $this->assertEquals($i, $stats['size']);
        }
        $this->assertEquals(10, $zip->numFiles);
        $zip->close();
    }
    
    public function tearDown()
    {
        for ($i = 0; $i < 10; $i++) {
            $filename = sprintf('tempfile%s.txt', $i);
            @unlink($this->app['app.var'] . DIRECTORY_SEPARATOR . $filename);
        }
        @unlink($this->app['app.var'] . DIRECTORY_SEPARATOR . 'archive.zip');
        
        parent::tearDown();
    }
}
