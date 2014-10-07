<?php
namespace Tests\Ilmatar\Helper;

use \Ilmatar\HelperFactory;
use \Ilmatar\Tests\AbstractTestCase;

class IntlHelperTest extends AbstractTestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        $this->helper = HelperFactory::build(
            'IntlHelper',
            ['locale' => 'fr']
        );
    }
    /**
     * @group IntlHelperTest
     * @group IntlHelperTest::testGetCountries
     */
    public function testGetCountries()
    {
        $countries = $this->helper->getCountryNames();
        $this->assertCount(259, $countries);
        $this->assertEquals('Italie', $countries['IT']);
    }
}
