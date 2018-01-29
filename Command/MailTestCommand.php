<?php

namespace JLaso\MailQueueBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class MailTestCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $dryRun = false;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('jlaso:mail:test')
            ->setDescription('send a test mail')
            ->addArgument('to', InputArgument::REQUIRED, 'the destination of the testing email')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'the sender of the testing email');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $to = $input->getArgument('to');
        $from = $input->getOption('from') ?: 'test@example.com';

        $output->writeln(sprintf("[%s] Sending a testing mail to `%s`...", date('Y-m-d H:i'), $to));

        /** @var \Swift_Mailer $mailer */
        $mailer = $this->container->get('mailer');

        $message = \Swift_Message::newInstance()
            ->setSubject('This is a very simple email test')
            ->setFrom($from)
            ->setSender($from)
            ->setTo($to)
            ->setBody('The simplest ever testing email', 'text/html');

        $delivered = $mailer->send($message);

        $output->writeln($delivered ? 'sent!' : 'unsent !!');
    }
}
