<?php

/*
 * eclipse-wiki
 */

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class SecondScreenDaemonTest extends KernelTestCase
{

    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('popup:vnc');
        $commandTester = new CommandTester($command);
        $this->expectException(\RuntimeException::class);
        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('VNC', $output);
    }

}
