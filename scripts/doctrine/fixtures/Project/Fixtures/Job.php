<?php
namespace Project\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use \Ilmatar\Command\Jobby as Jobby;

class Job extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $em)
    {
        $job = new \Entities\Job();
        $job->setCode(Jobby\CompileKpiCommand::COMMAND_CODE);
        $job->setDescription('Retrieve daily KPIs ans store values into DB');
        $job->setClass('CompileKpiCommand');
        $this->isClassExist('CompileKpiCommand');
        $job->setSchedule('10 0 * * * ');//At 0:10AM
        $em->persist($job);  

        $job = new \Entities\Job();
        $job->setCode(Jobby\SendMailCommand::COMMAND_CODE);
        $job->setDescription('Send a bunch of mails in waiting queue');
        $job->setClass('SendMailCommand');
        $this->isClassExist('SendMailCommand');
        $job->setSchedule('* * * * * ');//Every minute
        $em->persist($job); 
        
        $job = new \Entities\Job();
        $job->setCode(Jobby\SendReportCommand::COMMAND_CODE);
        $job->setDescription('Send query reports');
        $job->setClass('SendReportCommand');
        $this->isClassExist('SendReportCommand');
        $job->setSchedule('5 0 * * * ');//At 0:05AM
        $em->persist($job); 
        
        $em->flush();
    }

    public function getDependencies()
    {
        return array('Project\Fixtures\Role');
    }
   
    protected function isClassExist($classname)
    {
        $namespaces = [
            "\\Ilmatar\\Command\\Jobby\\",
            "\\Project\\Command\\Jobby\\"
        ];
        foreach ($namespaces as $namespace) {
            if (class_exists($namespace . $classname)) {
                return;
            }
        }
        throw new \Exception(sprintf("Unknown command class %s", $classname));
    }
}