<?php
namespace Tests\Ilmatar\Command;

use Ilmatar\Command\Jobby\CompileKpiCommand;
use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Carbon\Carbon;

class JobbyCompileKpiCommandTest extends AbstractTestCase
{
    /**
     * @group JobbyCompileKpiCommandTest
     * @group JobbyCompileKpiCommandTest::testCompileKpiCommand
     */
    public function testCompileKpiCommand()
    {
        $stub = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');

        $stub->expects($this->any())
            ->method('write')
            ->will($this->returnCallback(array(__CLASS__, 'mockWrite')));

        ob_start();
        $this->app['console']->run(new ArgvInput(['bin', 'jobby:' . CompileKpiCommand::COMMAND_CODE]), $stub);
        $return = ob_get_contents();
        ob_end_clean();
        $this->assertContains("Processing KPI KPI_NB_USERS", $return);
        $this->assertContains("Value for KPI KPI_NB_USERS today: 101", $return);
        $this->assertContains("Processing KPI KPI_NB_USERS2", $return);
        $this->assertContains("Value for KPI KPI_NB_USERS2 today: 101", $return);
        
        //relaunch
        ob_start();
        $this->app['console']->run(new ArgvInput(['bin', 'jobby:' . CompileKpiCommand::COMMAND_CODE]), $stub);
        $return = ob_get_contents();
        ob_end_clean();
        
        $this->assertCount(2, $this->app['orm.em']->getRepository('\\Entities\\KpiValue')->findBy(['date'=> Carbon::now()]));
    }

    public static function mockWrite($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        echo $messages;
    }

    /**
     * @group JobbyCompileKpiCommandTest
     * @group JobbyCompileKpiCommandTest::testConfigure
     */
    public function testConfigure()
    {
        $command = new CompileKpiCommand();
        $this->assertEquals('jobby:' . CompileKpiCommand::COMMAND_CODE, $command->getName());
        $this->assertEquals('Retrieve daily KPIs and store values into DB', $command->getDescription());
    }
}
