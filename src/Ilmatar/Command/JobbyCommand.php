<?php
namespace Ilmatar\Command;

use Ilmatar\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ilmatar\BaseCommand;
use Jobby\Jobby;
use Jobby\BackgroundJob;
use Jobby\Helper;

class JobbyCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('run:jobs')
            ->setDescription('Manage all internal jobs from one entry point (See cron_jobs table).')
            ->setHelp('Should be called from crontab (See console.xml)');
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
        //Jobby take its inputs from the job table which declares a set of console commands as jobby::job_code
        //Each job is launched by command "php app/console --jobby jobby::job_code"
        
        $app   = $this->getSilexApplication();
        $jobs = $app["orm.em"]->getRepository('\\Entities\\Job')->getJobsReady();
        foreach ($jobs as $job) {
            $output->write($job->getCode(), true);
            $jobby = new BackgroundJob(
                $job->getCode(),
                [
                    "dateFormat" => "Y-m-d H:i:s",
                    "maxRuntime" => null,
                    'enabled'    => true,
                    'schedule'   => $job->getSchedule(),
                    "runOnHost"  => (new Helper())->getHost(),
                    "output"     => null,
                    'command' => sprintf(
                        'cd %s && php app/console --jobby jobby:%s',
                        $app['app.root'],
                        $job->getCode()
                    ),
                ]
            );
            $jobby->run();
        }
    }
}
