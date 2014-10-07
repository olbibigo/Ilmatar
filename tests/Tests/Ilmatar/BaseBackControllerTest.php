<?php
namespace Tests\Ilmatar\Controller;

use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;

class BaseBackControllerTest extends AbstractTestCase
{
    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new \Project\Controller\PublicBackController($this->app);
    }

    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testBuildDataForJqPage
     */
    public function testBuildDataForJqPage()
    {
        $response = self::callMethod(
            $this->controller,
            'buildDataForJqPage',
            array(
                '\\Entities\\Parameter',
                'gridName',
                'fake-route',
                $this->app
            )
        );
        $this->assertInternalType('array', $response);
        $this->assertCount(11, $response);

        $this->assertTrue(isset($response['jqGridColNames']));
        $this->assertInternalType('array', $response['jqGridColNames']);
        $this->assertCount(10, $response['jqGridColNames']);
        $this->assertEquals('Id', $response['jqGridColNames'][0]);

        $this->assertTrue(isset($response['jqGridColModels']));
        $this->assertInternalType('array', $response['jqGridColModels']);
        $this->assertEquals(count($response['jqGridColNames']), count($response['jqGridColModels']));
        foreach ($response['jqGridColModels'] as $field) {
            switch($field['name']) {
                case 'id':
                    $this->assertFalse($field['editable']);
                    $this->assertCount(6, $field['searchoptions']['sopt']);
                    break;
                case 'created_by':
                case 'updated_by':
                    $this->assertFalse($field['editable']);
                    $this->assertCount(7, $field['searchoptions']['sopt']);
                    break;
                case 'created_at':
                case 'updated_at':
                    $this->assertFalse($field['editable']);
                    $this->assertTrue($field['hidden']);
                    $this->assertCount(4, $field['searchoptions']['sopt']);
                    break;
                case 'deleted_at':
                case 'deleted_by':
                    $this->assertTrue(false);//Dummy wrong assert executed if keys found
                default:
                    //Nothing
            }
        }

        $this->assertTrue(isset($response['jqGridColGroups']));
        $this->assertTrue(is_array($response['jqGridColGroups']) && empty($response['jqGridColGroups']));

        $this->assertTrue(isset($response['jqGridFooterData']));
        $this->assertTrue(is_array($response['jqGridFooterData']) && empty($response['jqGridFooterData']));

        $this->assertTrue(isset($response['jqGridDataReadUrl']));
        $this->assertEquals('/fake-route', $response['jqGridDataReadUrl']);

        $this->assertTrue(isset($response['jqGridName']));
        $this->assertEquals('gridName', $response['jqGridName']);

        $this->assertTrue(isset($response['jqInitialFilter']));
        $this->assertTrue(is_array($response['jqInitialFilter']) && empty($response['jqInitialFilter']));

        $this->assertTrue(isset($response['jqGridUserDataOnFooter']));
        $this->assertEquals('false', $response['jqGridUserDataOnFooter']);

    }

    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testBuildDataForEditableJqPage
     */
    public function testBuildDataForEditableJqPage()
    {
        $response = self::callMethod(
            $this->controller,
            'buildDataForEditableJqPage',
            array(
                '\\Entities\\Parameter',
                'gridName',
                'fake-route',
                'fake-route',
                $this->app
            )
        );
        $this->assertInternalType('array', $response);
        $this->assertCount(13, $response);

        $this->assertTrue(isset($response['jqGridDataWriteUrl']));
        $this->assertEquals('/fake-route', $response['jqGridDataWriteUrl']);

        $this->assertTrue(isset($response['csrfToken']) && !empty($response['csrfToken']));
    }

    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testLoadJqPage
     */
    public function testLoadJqPage()
    {
        $request = new Request(
            array(),
            array(
                \Ilmatar\JqGrid::JQGRID_KEY_SEARCH  => false,
                \Ilmatar\JqGrid::JQGRID_KEY_FILTERS => '[]',
                \Ilmatar\JqGrid::JQGRID_KEY_PAGE    => 1,
                \Ilmatar\JqGrid::JQGRID_KEY_ROWS    => 25,
                \Ilmatar\JqGrid::JQGRID_KEY_SIDX    => '',
                \Ilmatar\JqGrid::JQGRID_KEY_SORD    => ''
            )
        );
        $response = self::callMethod(
            $this->controller,
            'loadJqPage',
            array(
                '\\Entities\\Parameter',
                $request,
                $this->app
            )
        );

        $this->assertInternalType('array', $response);
        $this->assertCount(5, $response);

        $this->assertTrue(isset($response['rows']));
        $this->assertCount(25, $response['rows']);
        $this->assertCount(10, array_keys($response['rows'][0]));

        $this->assertTrue(isset($response['page']));
        $this->assertEquals(1, $response['page']);

        $this->assertTrue(isset($response['records']));
        $this->assertEquals(\Project\Fixtures\Parameter::NB_PARAMETERS, $response['records']);

        $this->assertTrue(isset($response['total']));
        $this->assertEquals(4, $response['total']);

        $this->assertTrue(isset($response['userdata']));
        $this->assertEmpty($response['userdata']);
    }

    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testLoadJqPageWithSortAndPage
     */
    public function testLoadJqPageWithSortAndPage()
    {
        $expectedPage     = 2;
        $expectedPageSize = 50;
        $expectedStart    = 50;
        $expectedEnd      = 1;
        $request = new Request(
            array(),
            array(
                \Ilmatar\JqGrid::JQGRID_KEY_SEARCH  => false,
                \Ilmatar\JqGrid::JQGRID_KEY_FILTERS => '[]',
                \Ilmatar\JqGrid::JQGRID_KEY_PAGE	=> $expectedPage,
                \Ilmatar\JqGrid::JQGRID_KEY_ROWS    => $expectedPageSize,
                \Ilmatar\JqGrid::JQGRID_KEY_SIDX    => 'id',
                \Ilmatar\JqGrid::JQGRID_KEY_SORD    => 'desc'
            )
        );
        $response = self::callMethod(
            $this->controller,
            'loadJqPage',
            array(
                '\\Entities\\Parameter',
                $request,
                $this->app
            )
        );

        $this->assertInternalType('array', $response);
        $this->assertCount(5, $response);

        $this->assertTrue(isset($response['page']));
        $this->assertEquals(2, $response['page']);

        $this->assertTrue(isset($response['rows']));
        $length = count($response['rows']);
        $this->assertEquals($expectedPageSize, $length);
        $this->assertEquals($expectedStart, $response['rows'][0]['id']);
        $this->assertEquals($expectedEnd, $response['rows'][$length-1]['id']);
    }

    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testLoadJqPageWithFilter
     */
    public function testLoadJqPageWithFilter()
    {
        $request = new Request(
            array(),
            array(
                \Ilmatar\JqGrid::JQGRID_KEY_SEARCH  => true,
                \Ilmatar\JqGrid::JQGRID_KEY_FILTERS => '{"groupOp":"AND","rules":[{"field":"id","op":"le","data":"10"}]}',
                \Ilmatar\JqGrid::JQGRID_KEY_PAGE    => 1,
                \Ilmatar\JqGrid::JQGRID_KEY_ROWS    => 10,
                \Ilmatar\JqGrid::JQGRID_KEY_SIDX    => 'id',
                \Ilmatar\JqGrid::JQGRID_KEY_SORD    => 'asc'
            )
        );
        $response = self::callMethod(
            $this->controller,
            'loadJqPage',
            array(
                '\\Entities\\Parameter',
                $request,
                $this->app
            )
        );

        $this->assertInternalType('array', $response);
        $this->assertCount(5, $response);

        $this->assertTrue(isset($response['page']));
        $this->assertEquals(1, $response['page']);

        $this->assertTrue(isset($response['rows']));
        $length = count($response['rows']);
        $this->assertEquals(10, $length);
        $this->assertEquals(1, $response['rows'][0]['id']);
        $this->assertEquals(10, $response['rows'][$length-1]['id']);
    }

    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testLoadJqPageWithFooter
     */
    public function testLoadJqPageWithFooter()
    {
        $request = new Request(
            array(),
            array(
                \Ilmatar\JqGrid::JQGRID_KEY_SEARCH  => false,
                \Ilmatar\JqGrid::JQGRID_KEY_PAGE	=> 1,
                \Ilmatar\JqGrid::JQGRID_KEY_ROWS    => 10,
                \Ilmatar\JqGrid::JQGRID_KEY_SIDX    => '',
                \Ilmatar\JqGrid::JQGRID_KEY_SORD    => ''
            )
        );
        $response = self::callMethod(
            $this->controller,
            'loadJqPage',
            array(
                '\\Entities\\Pipo',
                $request,
                $this->app,
                true,
                array(
                    \Ilmatar\BaseBackController::OPTION_HAS_FOOTER     => true,
                    \Ilmatar\BaseBackController::OPTION_HAS_FULL_TOTAL => true
                )
            )
        );

        $this->assertInternalType('array', $response);
        $this->assertCount(5, $response);
        $this->assertCount(2, $response['userdata']);
        $this->assertEquals("<span>TOTAL DE PAGE</span><br/><span>TOTAL</span>", $response['userdata']['id']);
        $this->assertRegExp('/<span>.*<\/span><br\/><span>.*<\/span>/u', $response['userdata']['value']);
    }

    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testSelectJqPage
     */
    public function testSelectJqPage()
    {
        $response = self::callMethod(
            $this->controller,
            'selectJqPage',
            array(
                '\\Entities\\Parameter',
                array(
                    'is_readonly' => true
                ),
                array(
                    'id' => 'DESC'
                ),
                'id',
                'code',
                $this->app['orm.em']
            )
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $content = $response->getContent();
        $this->assertEquals(\Project\Fixtures\Parameter::NB_PARAMETERS, substr_count($content, '</option>'));
        preg_match_all("/value=\"(.*?)\">/", $content, $matches);
        $this->assertCount(\Project\Fixtures\Parameter::NB_PARAMETERS, $matches[1]);
        $this->assertEquals(\Project\Fixtures\Parameter::NB_PARAMETERS, intval($matches[1][0]));
        $this->assertEquals(1, intval($matches[1][\Project\Fixtures\Parameter::NB_PARAMETERS-1]));
    }

    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testLoadJqPageForExport
     */
    public function testLoadJqPageForExport()
    {
        $request = new Request(
            array(),
            array(
                \Ilmatar\JqGrid::JQGRID_KEY_SEARCH  => false,
                \Ilmatar\JqGrid::JQGRID_KEY_FILTERS => '[]',
                \Ilmatar\JqGrid::JQGRID_KEY_PAGE	=> 1,//not used
                \Ilmatar\JqGrid::JQGRID_KEY_ROWS    => 50,//not used
                \Ilmatar\JqGrid::JQGRID_KEY_SIDX    => 'id',
                \Ilmatar\JqGrid::JQGRID_KEY_SORD    => 'desc'
            )
        );
        $response = self::callMethod(
            $this->controller,
            'loadJqPage',
            array(
                '\\Entities\\Parameter',
                $request,
                $this->app,
                false,
                array('isExport' => true)
            )
        );

        $this->assertInternalType('array', $response);
        $this->assertCount(4, $response);

        $this->assertTrue(isset($response['rows']));
        $this->assertCount(\Project\Fixtures\Parameter::NB_PARAMETERS, $response['rows']);
        $this->assertEquals(\Project\Fixtures\Parameter::NB_PARAMETERS, $response['rows'][0]['id']);
        $this->assertEquals(1, $response['rows'][\Project\Fixtures\Parameter::NB_PARAMETERS-1]['id']);

        $this->assertTrue(isset($response['records']));
        $this->assertEquals(\Project\Fixtures\Parameter::NB_PARAMETERS, $response['records']);

        $this->assertTrue(isset($response['sum']));
        $this->assertCount(1, $response['sum']);

        $this->assertTrue(isset($response['fullSum']));
        $this->assertCount(1, $response['fullSum']);
    }

    public function providerExportFormat()
    {
        return array(
            array(\Ilmatar\BaseBackController::EXPORT_FORMAT_PDF, 'application/pdf', '%PDF-1.4'),
            array(\Ilmatar\BaseBackController::EXPORT_FORMAT_XLS, 'application/vnd.ms-excel', null),
            array(\Ilmatar\BaseBackController::EXPORT_FORMAT_CSV, 'text/csv', 'Id;'),
            array(\Ilmatar\BaseBackController::EXPORT_FORMAT_XML, 'application/xml', '<?xml version="1.0" encoding="UTF-8"?>')
        );
    }
    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testExportData
     * @dataProvider providerExportFormat
     */
    public function testExportData($format, $mime, $key)
    {
        $request = new Request(
            array(),
            array(
                \Ilmatar\BaseBackController::EXPORT_FORMAT_PARAM_NAME      => $format,
                \Ilmatar\BaseBackController::EXPORT_ORIENTATION_PARAM_NAME => \Ilmatar\Helper\PdfHelper::EXPORT_ORIENTATION_PAYSAGE,
                \Ilmatar\BaseBackController::EXPORT_PERIMETER_PARAM_NAME   => \Ilmatar\BaseBackController::EXPORT_PERIMETER_FULL
            )
        );

        $keys = array();
        $data = $this->app['orm.em']->getRepository("\\Entities\\Parameter")->padAndFormatJqGridRows(
            array(
                array(
                    "id"    => "123",
                    "code"  => "THE_CODE",
                    "value" => "THE_VALUE"
                )
            ),
            false,
            array('id', 'code', 'value'),
            $keys
        );

        $response = self::callMethod(
            $this->controller,
            'exportData',
            array(
                $data,
                $request,
                $this->app,
                $keys
            )
        );

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        if ($key != null) {
            $this->assertStringStartsWith($key, $response->getContent());
        }
        $this->assertEquals($mime, $response->headers->get('Content-Type'));
    }

    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testExportPdfWithStyle
     */
    public function testExportPdfWithStyle()
    {
        $request = new Request(
            array(),
            array(
                \Ilmatar\BaseBackController::EXPORT_FORMAT_PARAM_NAME      => \Ilmatar\BaseBackController::EXPORT_FORMAT_PDF,
                \Ilmatar\BaseBackController::EXPORT_ORIENTATION_PARAM_NAME => \Ilmatar\Helper\PdfHelper::EXPORT_ORIENTATION_PORTRAIT,
                \Ilmatar\BaseBackController::EXPORT_PERIMETER_PARAM_NAME   => \Ilmatar\BaseBackController::EXPORT_PERIMETER_FULL
            )
        );

        $response = self::callMethod(
            $this->controller,
            'exportData',
            array(
                $this->app['orm.em']->getRepository("\\Entities\\Parameter")->padAndFormatJqGridRows(
                    array(
                        array(
                            "id"    => "123",
                            "code"  => "THE_CODE",
                            "value" => "THE_VALUE"
                        )
                    ),
                    false,
                    array('id', 'code', 'value')
                ),
                $request,
                $this->app,
                array(
                    \Ilmatar\BaseBackController::OPTION_HAS_FOOTER      => true,
                    \Ilmatar\BaseBackController::OPTION_HAS_FULL_TOTAL  => true,
                    \Ilmatar\BaseBackController::OPTION_IS_STRIPPED_TAG => true,
                    \Ilmatar\BaseBackController::OPTION_HIGHLIGHTS      => array(
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_FIRST_ROW,
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_LAST_ROW,
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_FIRST_COLUMN,
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_LAST_COLUMN,
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_ZEBRA_COLUMN,
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_ZEBRA_ROW
                    ),
                    \Ilmatar\BaseBackController::OPTION_STYLES => array(
                        "ul" => "font-weight:bold"
                    )
                )
            )
        );

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertStringStartsWith('%PDF-1.4', $response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

        /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testExportXlsWithStyle
     */
    public function testExportXlsWithStyle()
    {
        $request = new Request(
            array(),
            array(
                \Ilmatar\BaseBackController::EXPORT_FORMAT_PARAM_NAME      => \Ilmatar\BaseBackController::EXPORT_FORMAT_XLS,
                \Ilmatar\BaseBackController::EXPORT_ORIENTATION_PARAM_NAME => \Ilmatar\Helper\PdfHelper::EXPORT_ORIENTATION_PORTRAIT,
                \Ilmatar\BaseBackController::EXPORT_PERIMETER_PARAM_NAME   => \Ilmatar\BaseBackController::EXPORT_PERIMETER_FULL
            )
        );
        $keys = array();
        $data = $this->app['orm.em']->getRepository("\\Entities\\Parameter")->padAndFormatJqGridRows(
            array(
                array(
                    "id"    => "123",
                    "code"  => "THE_CODE",
                    "value" => "THE_VALUE",
                    "is_readonly" => "0",
                ),
                array(
                    "id"    => "1234",
                    "code"  => "THE_CODE2",
                    "value" => "THE_VALUE2",
                    "is_readonly" => "1",
                )
            ),
            false,
            array('id', 'code', 'value', 'is_readonly'),
            $keys
        );

        $response = self::callMethod(
            $this->controller,
            'exportData',
            array(
                $data,
                $request,
                $this->app,
                $keys,
                array(
                    \Ilmatar\BaseBackController::OPTION_HAS_FOOTER      => true,
                    \Ilmatar\BaseBackController::OPTION_HAS_FULL_TOTAL  => true,
                    \Ilmatar\BaseBackController::OPTION_IS_STRIPPED_TAG => true,
                    \Ilmatar\BaseBackController::OPTION_HIGHLIGHTS      => array(
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_FIRST_ROW,
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_LAST_ROW,
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_FIRST_COLUMN,
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_LAST_COLUMN,
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_ZEBRA_COLUMN,
                        \Ilmatar\Helper\ArrayHelper::HIGHLIGHT_ZEBRA_ROW
                    )
                )
            )
        );

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('application/vnd.ms-excel', $response->headers->get('Content-Type'));
    }

    public function providerChangeJqPage()
    {
        return array(
            array(\Ilmatar\JqGrid::JQGRID_ACTION_ADD, \Ilmatar\JqGrid::ID_NEW_ENTITY, '{"error":false,"id":101,"oper":"add"}'),
            array(\Ilmatar\JqGrid::JQGRID_ACTION_UPDATE, 1, '{"error":false,"id":1,"oper":"edit"}'),
            array(\Ilmatar\JqGrid::JQGRID_ACTION_DELETE, 1, '{"error":false,"id":1,"oper":"del"}')
        );
    }
    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testChangeJqPage
     * @dataProvider providerChangeJqPage
     */
    public function testChangeJqPage($oper, $id, $expected)
    {
        $token = self::callMethod($this->controller, 'generateToken', array($this->app));

        $request = new Request(
            array(),
            array(
                \Ilmatar\JqGrid::JQGRID_KEY_OPER          => $oper,
                \Ilmatar\BaseBackController::PARAM_TOKEN  => $token,
                'id'                                      => $id,
                'code'                                    => 'THE_CODE',
                'category'                                => 'CAT',
                'type'                                    => \Entities\Parameter::TYPE_STRING
            )
        );

        $response = self::callMethod(
            $this->controller,
            'changeJqPage',
            array(
                '\\Entities\\Parameter',
                $request,
                $this->app
            )
        );

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals($expected, $response->getContent());
    }

    public function providerProcessChange()
    {
        return array(
            array(
                \Ilmatar\JqGrid::JQGRID_ACTION_ADD,
                new \Entities\Parameter(
                    array(
                        'code'     => 'THE_CODE',
                        'category' => 'THE_CATEGORY',
                        'type'     => \Entities\Parameter::TYPE_STRING,
                        'value'    => 'THE_VALUE'
                    )
                ),
                "L'opération demandée s'est déroulée avec succès (id: 102)."
            ),
            array(
                \Ilmatar\JqGrid::JQGRID_ACTION_UPDATE,
                $this->app['orm.em']->find('\\Entities\\Parameter', 3),
                "L'opération demandée s'est déroulée avec succès (id: 3)."
            ),
            array(
                \Ilmatar\JqGrid::JQGRID_ACTION_DELETE,
                $this->app['orm.em']->find('\\Entities\\Parameter', 2),
                "L'opération demandée s'est déroulée avec succès (id: 2)."
            )
        );
    }
    /**
     * @group BaseBackControllerTest
     * @group BaseBackControllerTest::testProcessChange
     * @dataProvider providerProcessChange
     */
    public function testProcessChange($oper, $entity, $expected)
    {
        $this->app['session']->clear();
        $response = self::callMethod(
            $this->controller,
            'processChange',
            array(
                $entity,
                array(
                    \Ilmatar\JqGrid::JQGRID_KEY_OPER => $oper
                ),
                null,
                $this->app
            )
        );

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $fb   = $this->app['session']->getFlashBag()->all();
        $keys = array_keys($fb);
        $this->assertCount(1, $keys);
        $this->assertEquals('success', $keys[0]);
        $this->assertCount(1, $fb['success']);
        $this->assertEquals($expected, $fb['success'][0]);
    }
}
