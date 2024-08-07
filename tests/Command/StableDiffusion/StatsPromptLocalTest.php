<?php

/*
 * eclipse-wiki
 */

use App\Service\StableDiffusion\LocalRepository;
use App\Tests\Command\StableDiffusion\PngFixture;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class StatsPromptLocalTest extends KernelTestCase
{

    use PngFixture;

    public function testExecute()
    {
        $storage = self::getContainer()->get(LocalRepository::class);
        $application = new Application(self::$kernel);

        $this->insertFixturesInto($storage->getRootDir());
        // execute
        $command = $application->find('s:l:s');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $this->deleteFixturesInto($storage->getRootDir());

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('strawberry', $output);
    }

}
