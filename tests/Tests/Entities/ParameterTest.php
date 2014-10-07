<?php
namespace Tests\Entities;

use Ilmatar\Tests\AbstractTestCase;

class ParameterTest extends AbstractTestCase
{
    /**
     * @group ParameterTest
     * @group ParameterTest::testType
     */
    public function testType()
    {
        $parameter = $this->app['orm.em']->find('\\Entities\\Parameter', 1);
        $this->assertEquals(\Entities\Parameter::TYPE_BOOLEAN, $parameter->getType());
        $this->assertTrue(is_bool($parameter->getValue()));
        $this->assertEquals(true, $parameter->getValue());
        $parameter->setValue("0");
        $this->assertEquals(false, $parameter->getValue());

        $parameter = $this->app['orm.em']->find('\\Entities\\Parameter', 2);
        $this->assertEquals(\Entities\Parameter::TYPE_STRING, $parameter->getType());
        $this->assertTrue(is_string($parameter->getValue()));
        $this->assertEquals("Hello world!", $parameter->getValue());
        $parameter->setValue("12.34");
        $this->assertEquals("12.34", $parameter->getValue());
        
        $parameter = $this->app['orm.em']->find('\\Entities\\Parameter', 3);
        $this->assertEquals(\Entities\Parameter::TYPE_INTEGER, $parameter->getType());
        $this->assertTrue(is_int($parameter->getValue()));
        $this->assertEquals(666, $parameter->getValue());
        $parameter->setValue("12.34");
        $this->assertEquals(12, $parameter->getValue());
        
        $parameter = $this->app['orm.em']->find('\\Entities\\Parameter', 4);
        $this->assertEquals(\Entities\Parameter::TYPE_FLOAT, $parameter->getType());
        $this->assertTrue(is_float($parameter->getValue()));
        $this->assertEquals(666.66, $parameter->getValue());
        $parameter->setValue("12 apples");
        $this->assertEquals(12, $parameter->getValue());
    }
}
