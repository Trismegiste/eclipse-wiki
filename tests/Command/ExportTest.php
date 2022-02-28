<?php

/*
 * eclipse-wiki
 */

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ExportTest extends KernelTestCase
{

    public function testExecute()
    {
        $target = 'export.zip';
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('db:export');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['target' => $target]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Storage Added', $output);
        $this->assertFileExists($target);
        unlink($target);
    }

}
