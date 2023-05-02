<?php

/*
 * eclipse-wiki
 */

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class DumperTest extends KernelTestCase
{

    public function getCategory(): array
    {
        return [
            ['Compétence', 2],
            ['Atout', 2],
            ['Handicap', 2],
            ['Matériel', 2],
        ];
    }

    /** @dataProvider getCategory */
    public function testExecuteWithCategory(string $cat, int $limit)
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('mediawiki:dump-to-local');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['category' => $cat, '--limit' => $limit]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Dumping', $output);
    }

    public function testNonExistantCategory()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('mediawiki:dump-to-local');
        $commandTester = new CommandTester($command);
        $this->expectException(RuntimeException::class);
        $commandTester->execute(['category' => 'xxxx-yolo', '--limit' => 1]);
    }

}
