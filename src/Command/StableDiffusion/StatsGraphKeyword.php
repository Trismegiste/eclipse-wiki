<?php

/*
 * Eclipse Wiki
 */

namespace App\Command\StableDiffusion;

use App\Repository\CreationGraphProvider;
use App\Service\StableDiffusion\LocalRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Dump the text2img prompt keywords for the Creation Graph and counts how picture from InvokeAI local storage are
 */
#[AsCommand(name: 'sd:graph:keyword')]
class StatsGraphKeyword extends Command
{

    public function __construct(protected CreationGraphProvider $loader, protected LocalRepository $repository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(name: 'root-name', default: 'root');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Stats on local repository of InvokeAI pictures matched against Creation Graph keywords");

        $graph = $this->loader->load();
        $root = $graph->getNodeByName($input->getArgument('root-name'));
        $dump = $graph->accumulatePromptKeywordPerDistance($root);

        foreach ($dump as $level => $keywords) {
            if (count($keywords)) {
                $io->section("Level $level");
                foreach ($keywords as $keyword) {
                    $nb = count($this->repository->searchPicture($keyword));
                    $color = match (true) {
                        $nb === 0 => 'red',
                        $nb <= 3 => 'yellow',
                        $nb <= 10 => 'green',
                        default => 'white'
                    };
                    $io->text("<fg=$color>- $keyword : $nb</>");
                }
                $io->newLine();
            }
        }

        return self::SUCCESS;
    }

}
