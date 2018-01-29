<?php

namespace JLaso\MailQueueBundle;

use JLaso\MailQueueBundle\Service\MailQueueService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ProcessMailQueueCommand extends Command implements LoggerAwareInterface, ContainerAwareInterface
{
    use LoggerAwareTrait;
    use ContainerAwareTrait;

    protected $dryRun = false;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mail-queue:process')
            ->setDescription('process mail queue')
            ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, 'execute in dry-run mode', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf("[%s] Checking mail queue...", date('Y-m-d H:i')));

        /** @var MailQueueService $mailQueueService */
        $mailQueueService = $this->container->get('frontend_mail_queue_service');

        if ($mailQueueService->amIWorking()){
            $output->writeln('other instance is already running');
            return 0;
        }
        $mailQueueService->startWorking();

        try {
            $this->dryRun = $input->getOption('dry-run');
            /** @var \Swift_Mailer $mailer */
            $mailer = $this->container->get('mailer');

            $mails = $mailQueueService->getQueuedMails();

            foreach($mails as $mail => $data) {
                $message = \Swift_Message::newInstance()
                    ->setSubject($data['subject'])
                    ->setFrom($data['from'])
                    ->setSender($data['from'])
                    ->setTo($data['to'])
                    ->setBody($data['body'], 'text/html');

                $output->writeln(sprintf("Found '%s' mail from '%s' to '%s' subject '%s'", $mail, $data['from'], $data['to'], $data['subject']));

                $delivered = $mailer->send($message);

                $output->writeln($delivered ? 'sent!' : 'unsent !!');

                if ($delivered && !$this->dryRun) {
                    $mailQueueService->removeMail($mail);
                }
            }
        }catch(\Exception $e){
            $output->writeln($e->getMessage());
        }finally{
            $mailQueueService->finishWorking();
        }
    }
}
