<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class LoginLinkTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('auth:get-link');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--qrcode' => true]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('http', $output);
    }
}
