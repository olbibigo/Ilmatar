<?php
namespace Tests\Ilmatar\Command;

use Ilmatar\Command\UpdateVersionCommand;
use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

class UpdateVersionCommandTest extends AbstractTestCase
{
    public static function dataProviderUpdateVersion()
    {
        return array(
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:update-version',
                ),
                'expectedReturn'   => 'Not enough arguments.',
                'expectedVersions' => 19700103000000,
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'dbal:update-version',
                    '19700109000000',
                ),
                'expectedReturn'   => '',
                'expectedVersions' => 19700109000000,
            ),
        );
    }

    /**
     *
     *
     * @dataProvider dataProviderUpdateVersion
     *
     * @group UpdateVersionCommandTest
     * @group UpdateVersionCommandTest::testUpdateVersion
     */
    public function testUpdateVersion($data, $expectedReturn, $expectedVersion)
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
            $this->assertEquals($expectedReturn, $return);
        } catch (\RuntimeException $e) {
            $this->assertEquals($expectedReturn, $e->getMessage());
        }

        $this->assertEquals($expectedVersion, $this->app['orm.em']->getRepository('Entities\Version')->findAll()[0]->getVersion());
    }

    public static function mockWrite($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        echo $messages;
    }

    /**
     *
     *
     * @group UpdateVersionCommandTest
     * @group UpdateVersionCommandTest::testConfigure
     */
    public function testConfigure()
    {
        $command = new UpdateVersionCommand();
        $this->assertEquals('dbal:update-version', $command->getName());
        $this->assertEquals('Update the schema version', $command->getDescription());
        $this->assertEquals('Update the schema version.', $command->getHelp());

        $definition = new InputDefinition();
        $definition->addArgument(new InputArgument('version', InputArgument::REQUIRED, 'The new schema version.'));
        $this->assertEquals($definition, $command->getDefinition());
    }
}
