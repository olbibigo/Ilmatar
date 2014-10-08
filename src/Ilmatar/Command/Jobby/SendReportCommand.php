<?php
namespace Ilmatar\Command\Jobby;

use Ilmatar\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Carbon\Carbon;
use Entities\Query;
use Ilmatar\HelperFactory;

class SendReportCommand extends BaseJobbyCommand
{
    const COMMAND_CODE         = 'SEND_REPORT';
    const EXPORT_FILE_FORMAT   = "report_%s_%s.%s";
    const NOTIFICATION_SUBJECT = "%s: report '%s' (id: %s)";
    const NOTIFICATION_BODY    = "Hello,<br><br>Please, click the link below to download the new  available report.<br><br><a href='%s'>Report for %s</a> (%s rows, %s columns)<br/><br>To cancel your subscription, please contact the application administrator.<br/><br/>Regards.";
    
    protected $idxCurrentDay;
    protected $idxCurrentWeekday;
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('jobby:' . self::COMMAND_CODE)
            ->setDescription('Send query reports by mail with attachments');
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
        
        $currentDate = Carbon::now();
        $this->idxCurrentDay     = $currentDate->day;
        $this->idxCurrentWeekday = $currentDate->dayOfWeek;
      
        $app        = $this->getSilexApplication();
        $pathExport = $app['app.var']. '/export/';
        $queries    = $app['orm.em']->getRepository('\\Entities\\Query')->findBy(['is_exported' => true]);
        $pdfHelper        = HelperFactory::build(
            'PdfHelper',
            ['app.root'=> $app['app.root']]
        );
        
        foreach ($queries as $query) {
            $filename = sprintf(
                self::EXPORT_FILE_FORMAT,
                $query->getId(),
                $currentDate->format('Ymd'),
                strtolower(Query::getAllExportFormats()[$query->getExportFormat()])
            );
            $filepath = $pathExport . $filename;
            
            if ($this->canExport($query)) {
                $app['monolog.console']->info(sprintf('Processing query "%s" (id:%s).', $query->getName(), $query->getId()));
                try {
                    $results = $app['orm.ems']['r_only']->getConnection()->fetchAll($query->getQuery());
                } catch (\Exception $e) {
                    $app['monolog.console']->error('Invalid SQL syntax');
                    continue;
                }
                switch ($query->getExportFormat()){
                    case \Entities\Query::FORMAT_CSV:
                        $contents = HelperFactory::build('ArrayHelper')->getCsvFromArray($results, ';', $app['translator']);
                        break;
                    case \Entities\Query::FORMAT_PDF:
                        $contents = $pdfHelper->generatePdf(
                            '',
                            [
                                'title' => $query->getName(),
                                'body'  => HelperFactory::build('ArrayHelper')->getHtmlTableFromArray(
                                    $results,
                                    $app['translator']
                                )
                            ]
                        );
                        break;
                    case \Entities\Query::FORMAT_XLS:
                        $contents = HelperFactory::build('ExcelHelper')->createExport($results);
                        break;
                    case \Entities\Query::FORMAT_XML:
                        $contents = HelperFactory::build('ArrayHelper')->getXmlFromArray(
                            $results,
                            'items',
                            'item',
                            'full_string'
                        );
                        break;
                    default:
                        //Nothing
                }
                file_put_contents($filepath, $contents);

                if (!file_exists($filepath)) {
                    $app['monolog.console']->error(sprintf('%s cannot be written', $filepath));
                    continue;
                }
                $app['monolog.console']->info(sprintf('Written %s.', $filepath));
                $this->sendLink($query, count($results), count($results[0]), $currentDate, $filename, $app);
            }
        }

        $this->endExecute($input, $output);
    }
    
    protected function canExport(Query $query)
    {
        switch($query->getMailRepeats()) {
            case \Entities\Query::REPEAT_DAILY:
                return true;
            case \Entities\Query::REPEAT_WEEKLY:
                if ($this->idxCurrentWeekday == $query->getMailOffset()) {
                    return true;
                }
                break;
            case \Entities\Query::REPEAT_MONTHLY:
                if ($this->idxCurrentDay == $query->getMailOffset()) {
                    return true;
                }
                break;
            case self::REPEAT_DAILY:
            default:
                //Nothing
        }
        return false;
    }

    protected function sendLink(Query $query, $nbRows, $nbColumns, $currentDate, $filename, Application $app)
    {
        $tos = $query->getMailList();
        if (!is_null($tos)) {
            $mailHelper = HelperFactory::build(
                'MailHelper',
                [
                    'mailer'             => $app['mailer'],
                    'templateRepository' => $app['orm.em']->getRepository('\\Entities\\MailTemplate'),
                    
                ],
                [
                    'orm.em' => $app['orm.em'],
                    'logger' => $app['monolog.mailer']
                ]
            );
            $tos     = explode(',', $tos);
            $subject = sprintf(self::NOTIFICATION_SUBJECT, $app['app.name'], $query->getName(), $query->getId());
            $body    = sprintf(
                self::NOTIFICATION_BODY,
                str_replace(
                    ['{type}', '{token}'],
                    [\Project\Controller\PublicBackController::FILE_TYPE_REPORT, rawurlencode(HelperFactory::build('SecurityHelper')->encryptString($filename))],
                    $app['app.link.pattern']
                ),
                $currentDate->format('D, d M Y (H:i)'),
                $nbRows,
                $nbColumns
            );
            foreach ($tos as $to) {
                $app['monolog.console']->info(sprintf('Sending report to recipient %s', $to));
                //Check if recipient is a user and get his locale.
                $locale = $app['orm.em']->getRepository('\\Entities\\user')->getLocaleFromMail($to);
                if (($locale === $app['locale']) && ($app['locale'] !== 'en')) {
                    $result = $mailHelper->createAsynchronousMessage(
                        sprintf($app['translator']->trans(self::NOTIFICATION_SUBJECT), $app['app.name'], $query->getName(), $query->getId()),
                        sprintf(
                            $app['translator']->trans(self::NOTIFICATION_BODY),
                            str_replace(
                                ['{type}', '{token}'],
                                [\Project\Controller\PublicBackController::FILE_TYPE_REPORT, rawurlencode(HelperFactory::build('SecurityHelper')->encryptString($filename))],
                                $app['app.link.pattern']
                            ),
                            $currentDate->format('D, d M Y (H:i)'),
                            $nbRows,
                            $nbColumns
                        ),
                        $to
                    );
                } else {
                    $result = $mailHelper->createAsynchronousMessage(
                        $subject,
                        $body,
                        $to
                    );
                }
                if (!$result instanceof \Entities\Mail) {
                    $app['monolog.console']->error(sprintf('Cannot send to %s.', $to));
                }
            }
        }
    }
}
