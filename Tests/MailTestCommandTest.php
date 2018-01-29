<?php

namespace JLaso\MailQueueBundle\Tests;

use JLaso\MailQueueBundle\Command\MailTestCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class MailTestCommandTest extends WebTestCase
{
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
    }

    public function testExecute()
    {
        $application = new Application(static::$kernel);
        $theCommand = new MailTestCommand();
        $application->add($theCommand);

        $command = $application->find('jlaso:mail:test');
        $command->setContainer(static::$kernel->getContainer());
        $commandTester = new CommandTester($command);

        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--dry-run' => true,
                'mail@example.com' => null,
            )
        );

        $output = $commandTester->getDisplay();

        $this->assertRegExp('/Fail to send/', $output);
    }
}
