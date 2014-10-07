<?php
namespace Tests\Project;

use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebserviceControllerTest extends AbstractTestCase
{
    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new \Project\Controller\WebserviceController($this->app);
        $this->app['webservices'] = array(
            'isActive'    => true,
            'https'       => false,
            'credentials' => array(
                'credential' => array(
                    array(
                        'public.key'      => 'KQtEmHerKheNXTkR6JzmsrBC',
                        'shared.key'      => 'bvx5AvhDbChCjMFt5T4Yp6Yg',
                        'isActive'        => true,
                        'allowed.ips'     => '.*',
                        'allowed.domains' => '.*'
                    )
                )
            )
        );
    }
    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testNotActive
     */
    public function testNotActive()
    {
        $this->app['webservices'] = array(
            'isActive'    => false
        );

        $response = self::callMethod(
            $this->controller,
            'getEntityAction',
            array(
                'pipo',
                1,
                new Request(
                    ['token' => '']
                ),
                $this->app
            )
        );
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals("Not active service", $response->getContent());
    }
    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testWrongIP
     */
    public function testWrongIP()
    {
        $this->app['webservices'] = array(
            'isActive'    => true,
            'https'       => false,
            'credentials' => array(
                'credential' => array(
                    array(
                        'public.key'      => 'KQtEmHerKheNXTkR6JzmsrBC',
                        'shared.key'      => 'bvx5AvhDbChCjMFt5T4Yp6Yg',
                        'isActive'        => true,
                        'allowed.ips'     => '127.0.2',
                        'allowed.domains' => '.*'
                    )
                )
            )
        );
        
        $response = self::callMethod(
            $this->controller,
            'getEntityAction',
            array(
                'pipo',
                1,
                new Request(
                    ['token' => $this->getSignature()]
                ),
                $this->app
            )
        );
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals("Not allowed IP", $response->getContent());
    }
    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testWrongDomain
     */
    public function testWrongDomain()
    {
        $this->app['webservices'] = array(
            'isActive'    => true,
            'https'       => false,
            'credentials' => array(
                'credential' => array(
                    array(
                        'public.key'      => 'KQtEmHerKheNXTkR6JzmsrBC',
                        'shared.key'      => 'bvx5AvhDbChCjMFt5T4Yp6Yg',
                        'isActive'        => true,
                        'allowed.ips'     => '.*',
                        'allowed.domains' => 'xxx'
                    )
                )
            )
        );
        
        $response = self::callMethod(
            $this->controller,
            'getEntityAction',
            array(
                'pipo',
                1,
                new Request(
                    ['token' => $this->getSignature()]
                ),
                $this->app
            )
        );
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals("Not allowed domain", $response->getContent());
    }
    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testMissingToken
     */
    public function testMissingToken()
    {
        $response = self::callMethod(
            $this->controller,
            'getEntityAction',
            array(
                'pipo',
                1,
                new Request(),
                $this->app
            )
        );
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals("Missing token", $response->getContent());
    }
    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testInvalidAuthkeyStruture
     */
    public function testInvalidAuthkeyStruture()
    {
        $response = self::callMethod(
            $this->controller,
            'getEntityAction',
            array(
                'pipo',
                1,
                new Request(
                    array('token' => base64_encode('xxx;xxx'))
                ),
                $this->app
            )
        );
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals("Invalid token structure", $response->getContent());
    }
    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testInvalidKey
     */
    public function testInvalidKey()
    {
        $response = self::callMethod(
            $this->controller,
            'getEntityAction',
            array(
                'pipo',
                1,
                new Request(
                    array('token' => base64_encode('xxx;xxx;xxx'))
                ),
                $this->app
            )
        );
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals("Invalid public key", $response->getContent());
    }
    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testWrongAuthentication
     */
    public function testWrongAuthentication()
    {
        $response = self::callMethod(
            $this->controller,
            'getEntityAction',
            array(
                'pipo',
                1,
                new Request(
                    array('token' => base64_encode('KQtEmHerKheNXTkR6JzmsrBC;xxx;xxx'))
                ),
                $this->app
            )
        );
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals("Wrong signature", $response->getContent());
    }
    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testWrongSignature
     */
    public function testWrongSignature()
    {
        $response = self::callMethod(
            $this->controller,
            'getEntityAction',
            array(
                'pipo',
                1,
                new Request(
                    array('token' => base64_encode('KQtEmHerKheNXTkR6JzmsrBC;salt;xxx'))
                ),
                $this->app
            )
        );
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals("Wrong signature", $response->getContent());
    }
    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testUnknownEntity
     */
    public function testUnknownEntity()
    {
        $response = self::callMethod(
            $this->controller,
            'getEntityAction',
            array(
                'xxx',
                1,
                new Request(
                    array('token' => $this->getSignature())
                ),
                $this->app
            )
        );
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals("Unknown entity", $response->getContent());
    }

    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testNotAllowedEntity
     */
    public function testNotAllowedEntity()
    {
        $response = self::callMethod(
            $this->controller,
            'getEntitiesAction',
            array(
                'user',
                new Request(
                    array('token' => $this->getSignature())
                ),
                $this->app
            )
        );
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals("Not allowed method for entity", $response->getContent());
    }

    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testGet
     */
    public function testGet()
    {
        $response = self::callMethod(
            $this->controller,
            'getEntityAction',
            array(
                'pipo',
                1,
                new Request(
                    array('token' => $this->getSignature())
                ),
                $this->app
            )
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('content-type'));
        $content = $response->getContent();
        $this->assertStringStartsWith('"[{', $content);
        $this->assertStringEndsWith('}]"', $content);
        $this->assertGreaterThan(100, strlen($content));
    }

    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testGetAll
     */
    public function testGetAll()
    {
        $response = self::callMethod(
            $this->controller,
            'getEntitiesAction',
            array(
                'pipo',
                new Request(
                    array('token' => $this->getSignature())
                ),
                $this->app
            )
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('content-type'));
        $content = $response->getContent();
        $this->assertStringStartsWith('"[{', $content);
        $this->assertStringEndsWith('}]"', $content);
        $this->assertGreaterThan(10000, strlen($content));
    }

    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testPost
     */
    public function testPost()
    {
        $response = self::callMethod(
            $this->controller,
            'postEntityAction',
            array(
                'pipo',
                new Request(
                    array(),
                    array(
                       'token'          => $this->getSignature(),//S1F0RW1IZXJLaGVOWFRrUjZKem1zckJDO3NhbHQ7ZjVmNDI4OGFhMGJkN2E3OGM4NmJiMmVkMzFhNzAzZjllMjFkOTE1YTVhODNhNWYyNWY0YmExNWE2OGNjNzM4Nw==
                       'user'           => 1,
                       'functionality'  => 2,
                       'mycheck'        => 1,
                       'value'          => 666,
                       'thetype_date'   => '2014-04-22',
                       'thedatetime_at' => '2014-05-07 15:33:54'
                    )
                ),
                $this->app
            )
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('101', $response->getContent());
        $obj = $this->app['orm.em']->find('\\Entities\\Pipo', 101);
        $this->assertEquals(666, $obj->getValue());
        $this->assertEquals(2, $obj->getFunctionality()->getId());
    }

    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testPut
     */
    public function testPut()
    {
        $response = self::callMethod(
            $this->controller,
            'putEntityAction',
            array(
                'pipo',
                1,
                new Request(
                    array(),
                    array(
                       'token' => $this->getSignature(),//S1F0RW1IZXJLaGVOWFRrUjZKem1zckJDO3NhbHQ7ZjVmNDI4OGFhMGJkN2E3OGM4NmJiMmVkMzFhNzAzZjllMjFkOTE1YTVhODNhNWYyNWY0YmExNWE2OGNjNzM4Nw==
                       'user'  => 2,
                       'value' => 666
                    )
                ),
                $this->app
            )
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
        $obj = $this->app['orm.em']->find('\\Entities\\Pipo', 1);
        $this->assertEquals(666, $obj->getValue());
        $this->assertEquals(2, $obj->getUser()->getId());
    }

    /**
     * @group WebserviceControllerTest
     * @group WebserviceControllerTest::testDelete
     */
    public function testDelete()
    {
        $response = self::callMethod(
            $this->controller,
            'deleteEntityAction',
            array(
                'pipo',
                3,
                new Request(
                    array(),
                    array(
                       'token' => $this->getSignature()//S1F0RW1IZXJLaGVOWFRrUjZKem1zckJDO3NhbHQ7ZjVmNDI4OGFhMGJkN2E3OGM4NmJiMmVkMzFhNzAzZjllMjFkOTE1YTVhODNhNWYyNWY0YmExNWE2OGNjNzM4Nw==
                    )
                ),
                $this->app
            )
        );
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
        $this->assertNull($this->app['orm.em']->find('\\Entities\\Pipo', 3));
    }
    
    protected function getSignature()
    {
        return base64_encode(
            $this->app['webservices']['credentials']['credential'][0]['public.key']. ';salt;' . hash_hmac('sha256', '/', 'salt' . $this->app['webservices']['credentials']['credential'][0]['shared.key'])
        );
    }
}
