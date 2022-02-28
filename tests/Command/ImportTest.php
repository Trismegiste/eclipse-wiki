<?php

/*
 * eclipse-wiki
 */

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportTest extends KernelTestCase
{

    public function testExecute()
    {
        $target = __DIR__ . '/export.zip';
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('db:import');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);
        $commandTester->execute(['source' => $target]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Import succeed', $output);
        $this->assertFileExists($target);
    }

}
