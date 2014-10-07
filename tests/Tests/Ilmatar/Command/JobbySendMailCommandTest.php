<?php
namespace Tests\Ilmatar\Command;

use Ilmatar\Command\Jobby\SendMailCommand;
use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Carbon\Carbon;

class JobbySendMailCommandTest extends AbstractTestCase
{
    /**
     * @group JobbySendMailCommandTest
     * @group JobbySendMailCommandTest::testSendMailCommand
     */
    public function testSendMailCommand()
    {
        $stub = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');

        $stub->expects($this->any())
            ->method('write')
            ->will($this->returnCallback(array(__CLASS__, 'mockWrite')));

        $mail = $this->app['orm.em']->getRepository('\\Entities\\Mail')->findOneBy(
            ['sent_at' => null, 'attempt_count' => 0, 'is_error' => false]
        );
        $this->assertInstanceOf('\Entities\Mail', $mail);
        
        ob_start();
        $this->app['console']->run(new ArgvInput(['bin', 'jobby:' . SendMailCommand::COMMAND_CODE]), $stub);
        $return = ob_get_contents();
        ob_end_clean();
        $this->assertContains("1 asynchronous mail(s) has just been sent.", $return);
        
        $this->app['orm.em']->refresh($mail);
        $this->assertEquals(1, $mail->getAttemptCount());
        $this->assertEquals(Carbon::now()->format('Y-m-d'), $mail->getSentAt()->format('Y-m-d'));
    }

    public static function mockWrite($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        echo $messages;
    }

    /**
     * @group JobbySendMailCommandTest
     * @group JobbySendMailCommandTest::testConfigure
     */
    public function testConfigure()
    {
        $command = new SendMailCommand();
        $this->assertEquals('jobby:' . SendMailCommand::COMMAND_CODE, $command->getName());
        $this->assertEquals('Send a bunch of mails in waiting queue', $command->getDescription());
    }
}
