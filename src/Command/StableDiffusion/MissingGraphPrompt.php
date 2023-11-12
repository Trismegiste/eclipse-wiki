<?php

/*
 * Eclipse Wiki
 */

namespace App\Command\StableDiffusion;

use App\Entity\CreationTree\Graph;
use App\Repository\CreationGraphProvider;
use App\Service\StableDiffusion\LocalRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Iterates on keywords in the Creation Graph and search for missing keywords combinations
 */
#[AsCommand(name: 'sd:missing:prompt')]
class MissingGraphPrompt extends Command
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
        $io->title("Generates all prompts from keywords in the Creation Graph and searches for missing prompts in InvokeAI local storage");

        $graph = $this->loader->load();

        $io->section("Stats on most common keywords in unmatched prompts");

        $listing = $this->recurBuildPrompt($graph, [], $input->getArgument('root-name'));
        $promptStats = [];

        $io->progressStart(count($listing));
        foreach ($listing as $prompt) {
            foreach ($prompt as $word) {
                if (!key_exists($word, $promptStats)) {
                    $promptStats[$word] = ['count' => 0, 'missing' => 0];
                }
                $promptStats[$word]['count']++;
            }
            $matching = $this->repository->searchPicture(implode(' ', $prompt));
            if (count($matching) == 0) {
                $promptStats[$word]['missing']++;
            }
            $io->progressAdvance();
        }
        $io->progressFinish();

        // sort
        uasort($promptStats, function (array $a, array $b): int {
            return ($b['missing'] / $b['count']) <=> ($a['missing'] / $a['count']);
        });

        // print
        foreach ($promptStats as $word => $counter) {
            $io->writeln(sprintf("$word : %.0f%% (%d/%d)", 100 - $counter['missing'] * 100 / $counter['count'], $counter['count'] - $counter['missing'], $counter['count']));
        }

        return 0;
    }

    protected function recurBuildPrompt(Graph $graph, array $prefix, string $parentName): array
    {
        $parent = $graph->getNodeByName($parentName);
        $combi = [$prefix];
        foreach ($parent->text2img as $word) {
            $tmp = $prefix;
            $tmp[] = $word;
            $combi[] = $tmp;
        }

        foreach ($combi as $newPrefix) {
            foreach ($parent->children as $child) {
                $prompts = $this->recurBuildPrompt($graph, $newPrefix, $child);
                foreach ($prompts as $prompt) {
                    $combi[] = $prompt;
                }
            }
        }

        return $combi;
    }

}
