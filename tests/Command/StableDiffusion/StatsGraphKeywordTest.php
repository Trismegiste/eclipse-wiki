<?php

/*
 * eclipse-wiki
 */

use App\Service\StableDiffusion\LocalRepository;
use App\Tests\Command\StableDiffusion\PngFixture;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class StatsGraphKeywordTest extends KernelTestCase
{

    use PngFixture;

    public function testExecute()
    {
        $storage = self::getContainer()->get(LocalRepository::class);
        $provider = self::getContainer()->get(App\Repository\CreationGraphProvider::class);
        $graph = $provider->load();
        $root = $graph->getNodeByName('root');
        $root->children[] = 'fruit';
        $child = new \App\Entity\CreationTree\Node('fruit');
        $child->text2img = ['strawberry'];
        $graph->node[] = $child;
        $provider->save($graph);
        
        $application = new Application(self::$kernel);

        $this->insertFixturesInto($storage->getRootDir());
        // execute
        $command = $application->find('s:g:k');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $this->deleteFixturesInto($storage->getRootDir());

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('strawberry : 1', $output);
    }

}
