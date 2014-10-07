<?php
namespace Ilmatar\Tests;

use Ilmatar\Application;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Ilmatar\Application|null
     */
    protected $app;

    /**
     * {@inheritdoc}
     *
     * @param  string $name
     * @param  array  $data
     * @param  string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->app = Application::getInstance();
        $this->app['session.test'] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->app['orm.em']->beginTransaction();
        $this->app['session']->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->app['orm.em']->rollback();
    }
    
    public static function callMethod($obj, $name, array $args = [])
    {
        $class  = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }
}
