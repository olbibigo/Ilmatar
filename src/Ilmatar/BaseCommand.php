<?php
namespace Ilmatar;

use Ilmatar\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;

abstract class BaseCommand extends \Knp\Command\Command
{
    protected $logger = null;
    
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->prepareLogging($this->getSilexApplication(), $output);
    }

    protected function prepareLogging(Application $app, ConsoleOutput $output)
    {
        //Logging in both dedicated file and console
        if (isset($app['monolog.console'])) {
            $output->setVerbosity(
                $app['debug']
                ? OutputInterface::VERBOSITY_DEBUG
                : OutputInterface::VERBOSITY_VERY_VERBOSE
            );

            $app['monolog.console'] = $app->share(
                $app->extend(
                    'monolog.console',
                    function ($monolog, $app) use ($output) {
                        $monolog->pushHandler(new ConsoleHandler($output));
                        return $monolog;
                    }
                )
            );
             $this->logger = $app['monolog.console'];
        }
    }
}
