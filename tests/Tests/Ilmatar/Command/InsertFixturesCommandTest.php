<?php
namespace Tests\Ilmatar\Command;

use Ilmatar\Command\InsertFixturesCommand;
use Ilmatar\Tests\AbstractTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

class InsertFixturesCommandTest extends AbstractTestCase
{
    public static function dataProviderInsertFixtures()
    {
        return array(
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'orm:insert-fixtures',
                ),
                'expectedReturn'           => 'Not enough arguments.',
                'expectedNumberOfVersions' => 1,
            ),
            __LINE__ => array(
                'data' => array(
                    'bin',
                    'orm:insert-fixtures',
                    __DIR__ . '/../fixtures/fixtures',
                ),
                'expectedReturn'           => sprintf('Insert fixtures from %s directory', __DIR__ . '/../fixtures/fixtures'),
                'expectedNumberOfVersions' => 2,
            ),
        );
    }

    /**
     *
     *
     * @dataProvider dataProviderInsertFixtures
     *
     * @group InsertFixturesCommandTest
     * @group InsertFixturesCommandTest::testInsertFixtures
     */
    public function testInsertFixtures($data, $expectedReturn, $expectedNumberOfVersions)
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

        $this->assertCount($expectedNumberOfVersions, $this->app['orm.em']->getRepository('Entities\Version')->findAll());
    }

    public static function mockWrite($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        echo $messages;
    }

    /**
     *
     *
     * @group InsertFixturesCommandTest
     * @group InsertFixturesCommandTest::testConfigure
     */
    public function testConfigure()
    {
        $command = new InsertFixturesCommand();
        $this->assertEquals('orm:insert-fixtures', $command->getName());
        $this->assertEquals('Insert fixtures into database from a directory', $command->getDescription());
        $this->assertEquals('Insert fixtures from data-fixtures directory', $command->getHelp());

        $definition = new InputDefinition();
        $definition->addArgument(new InputArgument('directory', InputArgument::REQUIRED, 'Directory with fixtures classes'));
        $this->assertEquals($definition, $command->getDefinition());
    }
}
