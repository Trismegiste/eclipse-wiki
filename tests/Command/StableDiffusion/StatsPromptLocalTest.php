<?php

use App\Service\StableDiffusion\LocalRepository;
use App\Tests\Service\StableDiffusion\PngReaderTest;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/*
 * eclipse-wiki
 */

class StatsPromptLocalTest extends KernelTestCase
{

    public function testExecute()
    {
        $storage = self::getContainer()->get(LocalRepository::class);
        $application = new Application(self::$kernel);

        // inbsert
        $folder = __DIR__ . '/../../fixtures';
        $src = join_paths($folder, PngReaderTest::fixture);
        $dst = join_paths($storage->getRootDir(), PngReaderTest::fixture);
        copy($src, $dst);

        // execute
        $command = $application->find('s:l:s');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        @unlink($dst);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('strawberry', $output);
    }

}
