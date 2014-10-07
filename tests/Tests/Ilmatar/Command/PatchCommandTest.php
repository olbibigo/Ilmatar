<?php
namespace Tests\Ilmatar\Command;

use Ilmatar\Command\PatchCommand;
use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class PatchCommandTest extends AbstractTestCase
{
    public static function dataProviderUpdateVersion()
    {
        return array(
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'orm:patch',
                ),
                'expectedReturn'  => 'Not enough arguments.',
                'expectedVersion' => array('19700103000000'),
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'orm:patch',
                    __DIR__ . '/../fixtures/patches'
                ),
                'expectedReturn'  => "Option 'db-version' or 'class-name' is required in order to execute this command correctly.",
                'expectedVersion' => array('19700103000000'),
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'orm:patch',
                    __DIR__ . '/../fixtures/patches',
                    "--class-name=Version19700103000000\\Version123"
                ),
                'expectedReturn'  => "Class path Version19700103000000\\Version123 does not exist.",
                'expectedVersion' => array('19700103000000'),
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'orm:patch',
                    __DIR__ . '/../fixtures/patches',
                    "--class-name=Version19700103000000\\Version"
                ),
                'expectedReturn'  => 'Patch Version19700103000000\\Version processed',
                'expectedVersion' => array('19700103000000', '123'),
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'orm:patch',
                    __DIR__ . '/../fixtures/patches',
                    "--db-version=20000101000000"
                ),
                'expectedReturn'  => "No patch for version 20000101000000",
                'expectedVersion' => array('19700103000000'),
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'orm:patch',
                    __DIR__ . '/../fixtures/patches',
                    "--db-version=19700103000000"
                ),
                'expectedReturn'  => 'Patch version 19700103000000',
                'expectedVersion' => array('19700103000000', '456', '123'),
            ),
        );
    }

    /**
     *
     *
     * @dataProvider dataProviderUpdateVersion
     *
     * @group PatchCommandTest
     * @group PatchCommandTest::testPatch
     */
    public function testPatch($data, $expectedReturn, $expectedVersions)
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

        $versions = $this->app['orm.em']->getRepository('Entities\Version')->findAll();
        $dbVersions = array();
        foreach ($versions as $i => $version) {
            $dbVersions[] = $version->getVersion();
        }
        $this->assertEquals($expectedVersions, $dbVersions);
    }

    public static function mockWrite($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        echo $messages;
    }

    /**
     *
     *
     * @group PatchCommandTest
     * @group PatchCommandTest::testConfigure
     */
    public function testConfigure()
    {
        $command = new PatchCommand();
        $this->assertEquals('orm:patch', $command->getName());
        $this->assertEquals('Insert fixtures into database from a directory', $command->getDescription());
        $this->assertEquals('Patch the database', $command->getHelp());

        $definition = new InputDefinition();
        $definition->addArgument(new InputArgument('directory', InputArgument::REQUIRED, 'Directory with patches classes'));
        $definition->addOptions(array(
            new InputOption('db-version', null, InputOption::VALUE_OPTIONAL, 'Version to patch'),
            new InputOption('class-name', null, InputOption::VALUE_OPTIONAL, 'Full patch class name to run'),
        ));
        $this->assertEquals($definition, $command->getDefinition());
    }
}
