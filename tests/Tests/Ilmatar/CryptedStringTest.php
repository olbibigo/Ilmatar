<?php
namespace Tests\Ilmatar;

use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CryptedStringTest extends AbstractTestCase
{
    /**
     * @group CryptedStringTest
     * @group CryptedStringTest::testCryptedField
     */
    public function testCryptedField()
    {
        $pipo = $this->app['orm.em']->find('\Entities\Pipo', 1);
        $this->assertEquals(\Project\Fixtures\Pipo::CRYPTED_VALUE, $pipo->getCrypto());
    }
}
