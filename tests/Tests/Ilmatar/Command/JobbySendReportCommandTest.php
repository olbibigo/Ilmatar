<?php
namespace Tests\Ilmatar\Command;

use Ilmatar\Command\Jobby\SendReportCommand;
use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Carbon\Carbon;

class JobbySendReportCommandTest extends AbstractTestCase
{
    /**
     * @group JobbySendReportCommandTest
     * @group JobbySendReportCommandTest::testSendReportCommand
     */
    public function testSendReportCommand()
    {
        $stub = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');

        $stub->expects($this->any())
            ->method('write')
            ->will($this->returnCallback(array(__CLASS__, 'mockWrite')));
        
        $queries = $this->app['orm.em']->getRepository('\\Entities\\Query')->findBy(
            ['is_exported' => true]
        );
        $this->assertCount(2, $queries);
        
        //Set a user a receiver of a report
        $user  = $this->app['orm.em']->find('\\Entities\\User', 3);
        $query = $this->app['orm.em']->find('\\Entities\\Query', 3);
        $query->setMailList('xxx@xxx.com,' . $user->getUsername());
        $this->app['orm.em']->persist($query);
        $this->app['orm.em']->flush();
        
        ob_start();
        $this->app['console']->run(new ArgvInput(['bin', 'jobby:' . SendReportCommand::COMMAND_CODE]), $stub);
        $return = ob_get_contents();
        ob_end_clean();
     
        $report1 = sprintf("report_%s_%s.%s", 2, Carbon::now()->format('Ymd'), 'xml');
        $report2 = sprintf("report_%s_%s.%s", 3, Carbon::now()->format('Ymd'), 'csv');
        $path1   = $this->app['app.var']. '/export/' . $report1;
        $path2   = $this->app['app.var']. '/export/' . $report2;

        $this->assertContains("Processing query \"ROLES\" (id:2)", $return);
        $this->assertContains("export/" . $report1, $return);
        $this->assertContains("Processing query \"ROLES 2\" (id:3)", $return);
        $this->assertContains("export/" . $report2, $return);
        $this->assertContains("Sending report to recipient xxx@xxx.com", $return);
        $this->assertContains("Sending report to recipient " . $user->getUsername(), $return);
        $this->assertFileExists($path1);
        $this->assertFileExists($path2);

        $this->assertGreaterThan(10, filesize($path1));
        $this->assertGreaterThan(10, filesize($path2));

        $subject = sprintf("%s: report '%s' (id: %s)", $this->app['app.name'], 'ROLES 2', 3);
        $this->assertCount(1, $this->app['orm.em']->getRepository('\\Entities\\Mail')->findBy(['recipient' => 'xxx@xxx.com', 'object' => sprintf("%s: report '%s' (id: %s)", $this->app['app.name'], 'ROLES 2', 3)]));
        $this->assertCount(1, $this->app['orm.em']->getRepository('\\Entities\\Mail')->findBy(['recipient' => $user->getUsername(), 'object' => sprintf($this->app['translator']->trans("%s: report '%s' (id: %s)"), $this->app['app.name'], 'ROLES 2', 3)]));
        //Cleanup
        @unlink($path1);
        @unlink($path2);
    }

    public static function mockWrite($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        echo $messages;
    }

    /**
     * @group JobbySendReportCommandTest
     * @group JobbySendReportCommandTest::testConfigure
     */
    public function testConfigure()
    {
        $command = new SendReportCommand();
        $this->assertEquals('jobby:' . SendReportCommand::COMMAND_CODE, $command->getName());
        $this->assertEquals('Send query reports by mail with attachments', $command->getDescription());
    }
}
