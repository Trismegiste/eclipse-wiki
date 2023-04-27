<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PictureCensusTest extends KernelTestCase
{

    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('picture:unused');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--purge' => true]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('unused', $output);
    }

}
