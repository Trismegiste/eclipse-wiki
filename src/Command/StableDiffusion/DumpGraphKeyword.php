<?php

/*
 * Eclipse Wiki
 */

namespace App\Command\StableDiffusion;

use App\Repository\CreationGraphProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump the text2img prompt keywords for the Creation Graph
 */
#[AsCommand(name: 'sd:graph:keyword')]
class DumpGraphKeyword extends Command
{

    public function __construct(protected CreationGraphProvider $provider)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(name: 'root-name', default: 'root');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $graph = $this->provider->load();
        $root = $graph->getNodeByName($input->getArgument('root-name'));
        $dump = $graph->accumulatePromptKeywordPerDistance($root);

        foreach ($dump as $level => $keywords) {
            if (count($keywords)) {
                $output->writeln("==Level $level==");
                $keywords[] = '';
                $output->writeln($keywords);
            }
        }

        return self::SUCCESS;
    }

}
