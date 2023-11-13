<?php

/*
 * eclipse-wiki
 */

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class MissingGraphPromptTest extends KernelTestCase
{

    public function testExecute()
    {
        static::bootKernel();
        $application = new Application(static::$kernel);

        // execute
        $command = $application->find('s:m:p');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('unmatched', $output);
    }

}
