<?php

namespace JLaso\MailQueueBundle\Command;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class MailTestCommand extends Command implements LoggerAwareInterface, ContainerAwareInterface
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
            ->setName('mail:test')
            ->setDescription('send a test mail')
            ->addArgument('to', InputArgument::REQUIRED, 'the destination of the testing email');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $to = $input->getArgument('to');

        $output->writeln(sprintf("[%s] Sending a testing mail to `%s`...", date('Y-m-d H:i'), $to));

        /** @var \Swift_Mailer $mailer */
        $mailer = $this->container->get('mailer');

        $message = \Swift_Message::newInstance()
            ->setSubject('This is a very simple email test')
            ->setFrom('no-reply@digilant.com')
            ->setSender('no-reply@digilant.com')
            ->setTo($to , $to)
            ->setBody('The simplest ever testing email', 'text/html');

        $delivered = $mailer->send($message);

        $output->writeln($delivered ? 'sent!' : 'unsent !!');


    }
}
