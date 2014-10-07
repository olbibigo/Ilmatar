<?php
namespace Ilmatar\Command\Jobby;

use Ilmatar\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ilmatar\HelperFactory;

class SendMailCommand extends BaseJobbyCommand
{
    const COMMAND_CODE = 'SEND_MAIL';
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('jobby:' . self::COMMAND_CODE)
            ->setDescription('Send a bunch of mails in waiting queue');
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
        
        $app        = $this->getSilexApplication();
        $mailHelper = HelperFactory::build(
            'MailHelper',
            array(
                'mailer'             => $app['mailer'],
                'templateRepository' => $app['orm.em']->getRepository('\\Entities\\MailTemplate')
            ),
            array(
                'orm.em' => $app['orm.em'],
                'logger' => $app['monolog.mailer']
            )
        );
        
        $mails = $app['orm.em']->getRepository('\\Entities\\Mail')->getMailsToSend(
            $app['app.mail.attempt.count.max'],
            $app['app.mail.set.size']
        );

        $sentCounter = 0;
        foreach ($mails as $mail) {
            $return = $mailHelper->createAndSendMessage(
                $mail->getObject(),
                $mail->getBody(),
                array(//From
                    $app['app.mail.from.email'] => $app['app.mail.from.name']
                ),
                array($mail->getRecipient())//To
            );

            if ($return == 0) {
                $mail->markAsInvalid($app['app.mail.attempt.count.max']);
            } else {
                $sentCounter++;
                $mail->markAsSent();
            }
            $app['orm.em']->persist($mail);
        }
        $app['orm.em']->flush();
        $app['monolog.console']->info(sprintf('%s asynchronous mail(s) has just been sent.', $sentCounter));
        
        $this->endExecute($input, $output);
    }
}
