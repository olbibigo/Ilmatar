<?php
namespace Ilmatar\Command;

use Ilmatar\Application;
use Doctrine\Common\DataFixtures\Loader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Ilmatar\BaseCommand;

class InsertFixturesCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('orm:insert-fixtures')
            ->setDescription('Insert fixtures into database from a directory')
            ->setDefinition(array(
                new InputArgument('directory', InputArgument::REQUIRED, 'Directory with fixtures classes'),
            ))
            ->setHelp('Insert fixtures from data-fixtures directory');
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
        if (($dir = $input->getArgument('directory')) === null) {
            throw new \RuntimeException("Argument 'directory' is required in order to execute this command correctly.");
        }

        $output->write(sprintf('Insert fixtures from %s directory', $dir), true);

        $loader = new Loader();
        $loader->loadFromDirectory($dir);

        $purger = new ORMPurger();
        $executor = new ORMExecutor(Application::getInstance()['orm.em'], $purger);
        $executor->execute($loader->getFixtures(), true);
    }
}
