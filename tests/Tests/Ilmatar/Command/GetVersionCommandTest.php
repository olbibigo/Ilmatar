<?php
namespace Tests\Ilmatar\Command;

use Ilmatar\Command\GetVersionCommand;
use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class GetVersionCommandTest extends AbstractTestCase
{
    public static function dataProviderGetCurrentVersion()
    {
        return array(
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:get-version',
                    'last',
                    '--schemasPath=' . __DIR__ . '/../fixtures/schemas',
                ),
                'expected' => '19700105000000',
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:get-version',
                    'all',
                    '--schemasPath=' . __DIR__ . '/../fixtures/schemas',
                ),
                'expected' => '19700101000000,19700102000000,19700103000000,19700104000000,19700105000000',
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:get-version',
                    'previous',
                    '--schemasPath=' . __DIR__ . '/../fixtures/schemas',
                ),
                'expected' => '19700101000000,19700102000000',
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:get-version',
                    'next',
                    '--schemasPath=' . __DIR__ . '/../fixtures/schemas',
                ),
                'expected' => '19700104000000,19700105000000',
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:get-version',
                    'current',
                    '--schemasPath=' . __DIR__ . '/../fixtures/schemas',
                ),
                'expected' => '19700103000000',
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:get-version',
                    'last',
                ),
                'expected' => "Argument 'schemasPath' is required in order to execute this command correctly.",
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:get-version',
                    'all',
                ),
                'expected' => "Argument 'schemasPath' is required in order to execute this command correctly.",
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:get-version',
                    'previous',
                ),
                'expected' => "Argument 'schemasPath' is required in order to execute this command correctly.",
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:get-version',
                    'next',
                ),
                'expected' => "Argument 'schemasPath' is required in order to execute this command correctly.",
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:get-version',
                    'current',
                ),
                'expected' => '19700103000000',
            ),
        );
    }

    /**
     *
     * @dataProvider dataProviderGetCurrentVersion
     *
     * @group GetVersionCommandTest
     * @group GetVersionCommandTest::testGetCurrentVersion
     */
    public function testGetCurrentVersion($data, $expected)
    {

        $stub = $this->getMock('Symfony\Component\Console\Output\ConsoleOutput');

        $stub->expects($this->any())
            ->method('write')
            ->will($this->returnCallback(array(__CLASS__, 'mockWrite')));

        try {
            ob_start();
            $this->app['console']->run(new ArgvInput($data), $stub);
            $return = ob_get_contents();
            ob_end_clean();
            $this->assertEquals($expected, $return);
        } catch (\RuntimeException $e) {
            $this->assertEquals($expected, $e->getMessage());
        }
    }

    public static function mockWrite($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        echo $messages;
    }

    public function testConfigure()
    {
        $command = new GetVersionCommand();
        $this->assertEquals('dbal:get-version', $command->getName());
        $this->assertEquals('Tools for getting schema version informations.', $command->getDescription());
        $this->assertEquals('Return informations from schema versions.', $command->getHelp());

        $definition = new InputDefinition();
        $definition->addArgument(new InputArgument('get', InputArgument::REQUIRED, '(current | all | previous | next | last)'));
        $definition->addOption(new InputOption('schemasPath', null, InputOption::VALUE_OPTIONAL, 'Schemas Path'));
        $this->assertEquals($definition, $command->getDefinition());
    }
}
