<?php
namespace Ilmatar\Command\Jobby;

use Ilmatar\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Carbon\Carbon;

class CompileKpiCommand extends BaseJobbyCommand
{
    const COMMAND_CODE = 'KPI';
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('jobby:' . self::COMMAND_CODE)
            ->setDescription('Retrieve daily KPIs and store values into DB');
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
        $this->startExecute($input, $output);
        
        $app  = $this->getSilexApplication();
        $kpis = $app["orm.em"]->getRepository('\\Entities\\Kpi')->findBy(
            array(
                'is_active' => true
            )
        );
        $currentDate = Carbon::now();
        /*
         * Insert all daily stats declared into DB Kpi table
         */
        foreach ($kpis as $kpi) {
            $this->logger->info(sprintf("Processing KPI %s", $kpi->getCode()));
            $class = "\\Project\\Kpi\\" . $kpi->getClass();
            $obj   = new $class($currentDate, $kpi, $app["orm.em"], $this->logger);
            $obj->insert();
        }
        
        $this->endExecute($input, $output);
    }
}
