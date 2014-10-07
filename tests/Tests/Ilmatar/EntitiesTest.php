<?php
namespace Tests\Ilmatar;

use Ilmatar\Tests\AbstractTestCase;
use Ilmatar\HelperFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControllersTest extends AbstractTestCase
{
    protected $controller;
    protected $meta;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new \Project\Controller\PublicBackController($this->app);
        $this->metadata   = $this->app['orm.em']->getMetadataFactory()->getAllMetadata();
    }
    /**
     * @group ControllersTest
     * @group ControllersTest::testBuildDataForJqPage
     */
    public function testBuildDataForJqPage()
    {
        foreach ($this->metadata as $m) {
            $response = self::callMethod(
                $this->controller,
                'buildDataForJqPage',
                array(
                    $m->getName(),
                    'gridName',
                    'fake-route',
                    $this->app
                )
            );
            $this->assertInternalType('array', $response);
            
            $this->assertTrue(isset($response['jqGridColNames']));
            $this->assertInternalType('array', $response['jqGridColNames']);
            $this->assertGreaterThanOrEqual(1, count($response['jqGridColNames']));

            $this->assertTrue(isset($response['jqGridColModels']));
            $this->assertInternalType('array', $response['jqGridColModels']);
            $this->assertEquals(count($response['jqGridColNames']), count($response['jqGridColModels']));
        }
    }

    /**
     * @group ControllersTest
     * @group ControllersTest::testLoadJqPage
     */
    public function testLoadJqPage()
    {
        $request = new Request(
            array(),
            array(
                \Ilmatar\JqGrid::JQGRID_KEY_SEARCH  => false,
                \Ilmatar\JqGrid::JQGRID_KEY_FILTERS => '[]',
                \Ilmatar\JqGrid::JQGRID_KEY_PAGE	=> 1,
                \Ilmatar\JqGrid::JQGRID_KEY_ROWS    => 25,
                \Ilmatar\JqGrid::JQGRID_KEY_SIDX    => '',
                \Ilmatar\JqGrid::JQGRID_KEY_SORD    => ''
            )
        );

        foreach ($this->metadata as $m) {
            $response = self::callMethod(
                $this->controller,
                'loadJqPage',
                array(
                    $m->getName(),
                    $request,
                    $this->app,
                   true,
                   array('_locale' => 'en')
                )
            );
            $nbEntities = intval($this->app['orm.em']->createQuery(sprintf('SELECT COUNT(u.id) FROM %s u', $m->getName()))->getSingleScalarResult());

            $this->assertInternalType('array', $response);
            $this->assertCount(5, $response);
            $this->assertTrue(isset($response['rows']));
            $this->assertCount(min($nbEntities, 25), $response['rows']);

            if (0 != $nbEntities) {
                $keys = array_keys($response['rows'][0]);
                $this->assertEquals('id', $keys[0]);
            }

            $this->assertTrue(isset($response['page']));
            $this->assertEquals(1, $response['page']);

            $this->assertTrue(isset($response['records']));
            $this->assertEquals($nbEntities, $response['records']);

            $this->assertTrue(isset($response['total']));

            $this->assertTrue(isset($response['userdata']));
            $this->assertEmpty($response['userdata']);
        }


    }
}
