<?php
namespace Ilmatar\Command;

use Ilmatar\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ilmatar\BaseCommand;

class GetVersionCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dbal:get-version')
            ->setDescription('Tools for getting schema version informations.')
            ->setDefinition(array(
                new InputArgument('get', InputArgument::REQUIRED, '(current | all | previous | next | last)'),
                new InputOption('schemasPath', null, InputOption::VALUE_OPTIONAL, 'Schemas Path'),
            ))
            ->setHelp('Return informations from schema versions.');
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface  $input  Input interface
     * @param OutputInterface $output Output interface
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        if (($get = $input->getArgument('get')) === null) {
            throw new \RuntimeException("Argument 'get' is required in order to execute this command correctly.");
        }

        $currentVersion = false;
        if ($get != 'all' && $get != 'last') {
            $conn           = Application::getInstance()['db'];
            $result         = $conn->fetchAssoc("SELECT version FROM version");
            $currentVersion = count($result) === 0 ? false : $result['version'];
        }

        if ($get == 'current') {
            $output->write($currentVersion === false ? null : $currentVersion, true);
            return 1;
        }

        if ($get == 'previous' && $currentVersion === false) {
            $output->write(null, true);
            return 1;
        }

        if (($schemasPath = $input->getOption('schemasPath')) === null) {
            throw new \RuntimeException("Argument 'schemasPath' is required in order to execute this command correctly.");
        }

        $versions = $currentVersion === false ? [] : array($currentVersion);
        
        foreach (scandir($schemasPath) as $path) {
            if (is_dir($schemasPath  . '/' . $path) && preg_match('/^[0-9]{14}$/', $path) === 1) {
                if (! in_array($path, $versions, true)) {
                    $versions[] = $path;
                }
            }
        }
        sort($versions, SORT_STRING);
        
        if ($get == 'last') {
            $output->write(count($versions) === 0 ? null : end($versions), true);
            return 1;
        }

        $first = true;
        foreach ($versions as $version) {
            if (($get == 'all')
                || (($get == 'previous') && ($version < $currentVersion))
                || (($get == 'next') && (($currentVersion === false) || ($version > $currentVersion)))
            ) {
                $output->write(($first ? '' : ',') . $version);
                $first = false;
            }
        }
    }
}
