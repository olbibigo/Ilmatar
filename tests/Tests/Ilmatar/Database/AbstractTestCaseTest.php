<?php
namespace Tests\Ilmatar\Database;

use Ilmatar\Tests\AbstractTestCase;
use Entities\Version;

class AbstractTestCaseTest extends AbstractTestCase
{
    /**
     * Check if all test are in a transaction
     *
     * @return void
     *
     * @group AbstractTestCaseTest
     * @group AbstractTestCaseTest::testTransaction
     */
    public function testTransaction()
    {
        $version = new Version();
        $version->setVersion('123');
        $this->app['orm.em']->persist($version);
        $this->app['orm.em']->flush();
        $this->assertTrue(true);
    }

    /**
     * Check if all test are in a transaction
     *
     * @return void
     *
     * @group AbstractTestCaseTest
     * @group AbstractTestCaseTest::testTransaction2
     */
    public function testTransaction2()
    {
        $this->assertCount(1, $this->app['orm.em']->getRepository('Entities\Version')->findAll());
    }
}
