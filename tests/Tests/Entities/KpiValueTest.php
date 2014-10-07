<?php
namespace Tests\Entities;

use Ilmatar\Tests\AbstractTestCase;
use Repositories\KpiValue;

class KpiValueTest extends AbstractTestCase
{
    /**
     * @group KpiValueTest
     * @group KpiValueTest::testYearlyView
     */
    public function testYearlyView()
    {
        $values = $this->app['orm.em']->getRepository('\\Entities\\KpiValue')->getDataToDisplay(
            'KPI_NB_USERS',
            [\Repositories\KpiValue::PARAM_VIEW_NAME => \Entities\KpiValue::VIEW_YEAR]
        );
        $this->assertCount(1, $values);
        $this->assertCount(2, $values[0]);
        $this->assertArrayEquals(['2013-01-01', 365], $values[0]);
    }
    /**
     * @group KpiValueTest
     * @group KpiValueTest::testMonthlyView
     */
    public function testMonthlyView()
    {
        $values = $this->app['orm.em']->getRepository('\\Entities\\KpiValue')->getDataToDisplay(
            'KPI_NB_USERS',
            [\Repositories\KpiValue::PARAM_VIEW_NAME => \Entities\KpiValue::VIEW_MONTH]
        );
        $this->assertCount(12, $values);
        $this->assertCount(2, $values[0]);
        $this->assertArrayEquals(['2013-01-01', 31], $values[0]);
        $this->assertArrayEquals(['2013-12-01', 31], $values[11]);
    }
    /**
     * @group KpiValueTest
     * @group KpiValueTest::testWeeklyView
     */
    public function testWeeklyView()
    {
        $values = $this->app['orm.em']->getRepository('\\Entities\\KpiValue')->getDataToDisplay(
            'KPI_NB_USERS',
            [\Repositories\KpiValue::PARAM_VIEW_NAME => \Entities\KpiValue::VIEW_WEEK]
        );
        $this->assertCount(53, $values);
        $this->assertCount(2, $values[0]);
        $this->assertArrayEquals(['2012-12-31', 6], $values[0]);
        $this->assertArrayEquals(['2013-01-07', 7], $values[1]);
        $this->assertArrayEquals(['2013-12-23', 7], $values[51]);
        $this->assertArrayEquals(['2013-12-30', 2], $values[52]);
    }
    /**
     * @group KpiValueTest
     * @group KpiValueTest::testDailyView
     */
    public function testDailyView()
    {
        $values = $this->app['orm.em']->getRepository('\\Entities\\KpiValue')->getDataToDisplay(
            'KPI_NB_USERS',
            [\Repositories\KpiValue::PARAM_VIEW_NAME => \Entities\KpiValue::VIEW_DAY]
        );
        $this->assertCount(365, $values);
        $this->assertCount(2, $values[0]);
        $this->assertArrayEquals(['2013-01-01', 1], $values[0]);
        $this->assertArrayEquals(['2013-12-31', 1], $values[364]);
    }
    
    protected function assertArrayEquals($expected, $value)
    {
        $this->assertEmpty(array_merge(array_diff($expected, $value), array_diff($value, $expected)));
    }
}
