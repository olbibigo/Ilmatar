<?php
namespace Ilmatar\Command\Jobby;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ilmatar\BaseCommand;

class BaseJobbyCommand extends BaseCommand
{
    protected $startTimer;
    protected $job;
    /**
     * {@inheritdoc}
     *
     * @param InputInterface  $input  Input interface
     * @param OutputInterface $output Output interface
     *
     * @return void
     */
    protected function startExecute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        
        $app = $this->getSilexApplication();
        $this->job = $app["orm.em"]->getRepository('\\Entities\\Job')->findOneByCode(static::COMMAND_CODE);
        $this->job->setStatus(\Entities\Job::JOB_STATUS_RUNNING);
        $this->job->setRunCounter($this->job->getRunCounter() + 1);
        $app["orm.em"]->persist($this->job);
        $app["orm.em"]->flush();
        
        $this->startTimer = time();
    }
    
    protected function endExecute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();
        $this->job->setStatus(\Entities\Job::JOB_STATUS_READY);
        $this->job->setRunTime(time() - $this->startTimer);
        $this->job->setFinishedAt(new \DateTime());
        $app["orm.em"]->persist($this->job);
        $app["orm.em"]->flush();
    }
}
