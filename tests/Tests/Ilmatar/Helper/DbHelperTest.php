<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class DbHelperTest extends AbstractTestCase
{
    const ROW_NUMBER = 10;

    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'DbHelper',
            array(
               //Nothing right now
            )
        );
    }
    /**
     * @group DbHelperTest
     * @group DbHelperTest::testExecuteSqlAgainstArray
     */
    public function testExecuteSqlAgainstArray()
    {
        $metadata = array(
            array(
               'name' => 'column1',
               'type' => 'TEXT'
            ),
            array(
               'name' => 'column2',
               'type' => 'INTEGER'
            ),
            array(
               'name' => 'column3',
               'type' => 'REAL'
            )
            //Note: In SQLite, date should be stored as integer
            //Nevertheless, it can also be stored as string with
            //format ISO8601 strings ("YYYY-MM-DD HH:MM:SS.SSS").
            //See http://www.sqlite.org/datatype3.html
        );

        $data = array();
        for ($i = 0; $i < self::ROW_NUMBER; ++$i) {
            $data[] = array(
                'column1' => 'text',
                'column2' => $i,
                'column3' => rand(0, 1) / 10
            );
        }

        $this->assertCount(self::ROW_NUMBER, $this->helper->executeSqlAgainstArray($metadata, $data));
        $this->assertCount(5, $this->helper->executeSqlAgainstArray($metadata, $data, 'WHERE column2 < 5'));
        $results = $this->helper->executeSqlAgainstArray($metadata, $data, 'ORDER BY column1 DESC');
        $this->assertEquals(self::ROW_NUMBER - 1, $results[0]['column2']);
        $this->assertCount(self::ROW_NUMBER, $this->helper->executeSqlAgainstArray($metadata, $data, "WHERE column1 LIKE 'te%'"));
        $this->assertCount(0, $this->helper->executeSqlAgainstArray($metadata, $data, "WHERE column1 LIKE '%te'"));
        $this->assertCount(self::ROW_NUMBER, $this->helper->executeSqlAgainstArray($metadata, $data, "WHERE column3 >= 0.0 AND column3 <=1"));
    }
}
