<?php
namespace Ilmatar\Command;

use Ilmatar\Application;
use Doctrine\Common\DataFixtures\Loader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Ilmatar\BaseCommand;

class PatchCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('orm:patch')
            ->setDescription('Insert fixtures into database from a directory')
            ->setDefinition(array(
                new InputArgument('directory', InputArgument::REQUIRED, 'Directory with patches classes'),
                new InputOption('db-version', null, InputOption::VALUE_OPTIONAL, 'Version to patch'),
                new InputOption('class-name', null, InputOption::VALUE_OPTIONAL, 'Full patch class name to run'),
            ))
            ->setHelp('Patch the database');
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

        $version = $input->getOption('db-version');
        if (!is_null($version)) {
            $loadedDirectory = sprintf('%s/Version%s', $dir, $version);
            if (! file_exists($loadedDirectory)) {
                $output->write(sprintf('No patch for version %s', $version), true);
            } else {
                $output->write(sprintf('Patch version %s', $version), true);

                $loader = new Loader();
                $loader->loadFromDirectory($loadedDirectory);

                $purger = new ORMPurger();
                $executor = new ORMExecutor(Application::getInstance()['orm.em'], $purger);
                $executor->execute($loader->getFixtures(), true);
            }
        } elseif (($className = $input->getOption('class-name')) !== null) {
            $loadedClass = $dir . '/' . str_replace('\\', '/', $className) . '.php';
            if (! file_exists($loadedClass)) {
                throw new \RuntimeException(sprintf("Class path %s does not exist.", $className));
            }

            require_once $loadedClass;

            $loader = new Loader();
            $loader->addFixture(new $className());

            $purger   = new ORMPurger();
            $executor = new ORMExecutor(Application::getInstance()['orm.em'], $purger);
            $executor->execute($loader->getFixtures(), true);

            $output->write(sprintf('Patch %s processed', $className), true);

        } else {
            throw new \RuntimeException("Option 'db-version' or 'class-name' is required in order to execute this command correctly.");
        }
    }
}
