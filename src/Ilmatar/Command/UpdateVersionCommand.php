<?php
namespace Ilmatar\Command;

use Ilmatar\Application;
use Entities\Version;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ilmatar\BaseCommand;

class UpdateVersionCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dbal:update-version')
            ->setDescription('Update the schema version')
            ->setDefinition(array(
                new InputArgument('version', InputArgument::REQUIRED, 'The new schema version.'),
            ))
            ->setHelp('Update the schema version.');
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
        if (($version = $input->getArgument('version')) === null) {
            throw new \RuntimeException("Argument 'version' is required in order to execute this command correctly.");
        }

        /* @var \Doctrine\DBAL\Connection $db */
        $db = Application::getInstance()['db'];
        $db->executeQuery("DELETE FROM `version`");
        $db->insert('version', array('version' => $version));
    }
}
