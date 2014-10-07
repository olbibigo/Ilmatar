<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class SecurityHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'SecurityHelper',
            array(
               //Nothing right now
            ),
            array(
                'seedFile' => $this->app['app.var'] . '/seedFile.txt'
            )
        );
    }

    /**
     * @group SecurityHelperTest
     * @group SecurityHelperTest::testGeneratePassword
     */
    public function testGeneratePassword()
    {
        $this->assertEquals(32, strlen($this->helper->generatePassword(32)));
    }
    
    /**
     * @group SecurityHelperTest
     * @group SecurityHelperTest::testEncryptDecrypt
     */
    public function testEncryptDecrypt()
    {
        $input = "The Advanced Encryption Standard (AES) is the successor of triple DES. When you need a standardized, secure, high performance symmetric cipher it seems like a good choice. Wi-Fi network traffic is encrypted with AES for instance. Also when you want to securely store data in a database or on disk you could choose AES. Many SSDs store data internally using AES encryption. PHP supports AES through mcrypt.";
        $key   = "Rijndael-128";
        $this->assertEquals(
            $input,
            $this->helper->decryptString(
                $this->helper->encryptString(
                    $input,
                    $key
                ),
                $key
            )
        );
    }
}
