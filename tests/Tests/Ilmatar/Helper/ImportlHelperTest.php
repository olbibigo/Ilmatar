<?php
namespace Tests\Ilmatar\Helper;

use Ilmatar\HelperFactory;
use Ilmatar\Tests\AbstractTestCase;

class ImportHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'ImportHelper',
            array(
                'orm.em'     => $this->app['orm.em'],
                'translator' => $this->app['translator']
            ),
            array(
                "logger" => $this->app['monolog.import']
            )
        );
    }
    public function tearDown()
    {
        $this->app['orm.em']->clear();
        parent::tearDown();
    }
    
    /**
     * @group ImportHelperTest
     * @group ImportHelperTest::testGetAllEntities
     */
    public function testGetAllEntities()
    {
        $msg = $this->helper->getAllEntities();

        $this->assertEquals(array('Entities\\Pipo' => 'Pipo'), $msg);
    }
    
    /**
     * @group ImportHelperTest
     * @group ImportHelperTest::testGetEntityImportableField
     */
    /*public function testGetEntityImportableField()
    {
        $msg = $this->helper->getEntityImportableField('Entities\\User');

        $this->assertEquals(array(), $msg);
    }*/
    
    /**
     * @group ImportHelperTest
     * @group ImportHelperTest::testValidImportableFile1
     */
    public function testValidImportableFile1()
    {
        $path = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'toto';
        $msg = $this->helper->validImportableFile('Entities\\Pipo', $path);
        $this->assertEquals(array("Can't open this file ".$path), $msg["fatal"]);
    }

    /**
     * @group ImportHelperTest
     * @group ImportHelperTest::testImportFile1
     */
    public function testImportFile1()
    {
        // $path = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . "ImportTest.csv";
        // $npath = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . "ImportTest1.csv";
        // copy($path, $npath);
        // $msg = $this->helper->ImportFile('Entities\\Pipo', $npath, \Ilmatar\Helper\ImportHelper::MODE_ERASE);
        // $this->assertFileExists($npath . ".processed");
        // unlink($npath . ".processed");
        // $this->assertEquals(array(
            // 'Erreur ligne 7, un "user.city" est requis',
            // 'Erreur ligne 12, "J" n\'est pas un functionality.code valide',
            // 'Erreur ligne 17, Le champ "E-mail" doit contenir une adresse email valide.',
            // 'Erreur ligne 18, "Thetype date" ne peut pas être plus tôt que "Thedatetime at"',
            // 'Erreur ligne 20, "Thetype date" ne peut pas être plus tôt que "Thedatetime at"',
            // 'Erreur ligne 21, The separation symbol could not be found',
            // 'Erreur ligne 23, "Thetype date" ne peut pas être plus tôt que "Thedatetime at"',
            // 'Erreur ligne 25, Le champ "Mycheck" doit-être valide.',
            // 'Erreur ligne 27, Data missing',
            // 'Erreur ligne 30, un "mycheck" est requis',
            // 'Erreur ligne 101, "100" n\'est pas un user.city valide',
            // 'Erreur ligne 102, "TOTAL" n\'est pas un user.city valide',
            // 'Erreur ligne 102, un "mycheck" est requis',
            // 'Erreur ligne 102, un "thetype_date" est requis',
        // ), $msg["list"]["error"]);
        // $this->assertEquals(12, $msg["errors"]);
        // $this->assertEquals(89, $msg["count"]);
    }

    /**
     * @group ImportHelperTest
     * @group ImportHelperTest::testImportFile2
     */
    public function testImportFile2()
    {
        // $path = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'toto';

        // $msg = $this->helper->ImportFile('Entities\\Pipo', $path, \Ilmatar\Helper\ImportHelper::MODE_ERASE);
        // $this->assertEquals("fatal", $msg["errors"]);
        // $this->assertEquals("Can't open this file ".$path, $msg["list"]);
    }
    /**
     * @group ImportHelperTest
     * @group ImportHelperTest::testImportFile3
     */
    public function testImportFile3()
    {
        // $path = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . "Import2Test.csv";
        // $npath = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . "Import2Test1.csv";
        // copy($path, $npath);
        // $msg = $this->helper->ImportFile('Entities\\Pipo', $npath, \Ilmatar\Helper\ImportHelper::MODE_ADD);
        // $this->assertFileExists($npath);
        // unlink($npath);
        // $this->assertEquals("fatal", $msg["errors"]);
        // $this->assertEquals("La colonne user.city est requise", $msg["list"]);
    }
    /**
     * @group ImportHelperTest
     * @group ImportHelperTest::testImportFile5
     */
    public function testImportFile5()
    {
        // $path = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . "ImportTest.csv";
        // $npath = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . "ImportTest1.csv";
        // copy($path, $npath);
        // $msg = $this->helper->ImportFile('Entities\\Pipo', $npath, \Ilmatar\Helper\ImportHelper::MODE_ADD);
        // $this->assertFileExists($npath);
        // unlink($npath);
        // $this->assertEquals("fatal", $msg["errors"]);
        // $this->assertEquals("Unique constraint on 'UNIQ_FF4F8A10E7927C74' failed for value 'toto@caramail.com'.", $msg["list"]);
    }
    /**
     * @group ImportHelperTest
     * @group ImportHelperTest::testImportFile6
     */
    public function testImportFile6()
    {
        // $path = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . "ImportTest.csv";
        // $npath = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . "ImportTest1.csv";
        // copy($path, $npath);
        // $msg = $this->helper->ImportFile('Entities\\Pipo', $npath, \Ilmatar\Helper\ImportHelper::MODE_ERASE);
        // $this->assertFileExists($npath);
        // unlink($npath);
        // $this->assertEquals("fatal", $msg["errors"]);
        // $this->assertEquals("The EntityManager is closed.", $msg["list"]);
    }
    /**
     * @group ImportHelperTest
     * @group ImportHelperTest::testValidImportableFile2
     */
    public function testValidImportableFile2()
    {
        // $path = $this->app['app.var'] . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . "ImportTest.csv";
        // $msg = $this->helper->validImportableFile('Entities\\Pipo', $path);
        // $this->assertEquals(array("L'importation a échoué"), $msg["fatal"]);
    }
}
