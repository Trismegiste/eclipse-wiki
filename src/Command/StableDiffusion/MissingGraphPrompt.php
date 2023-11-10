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
        $io->title("Iterates on keywords in the Creation Graph and search for missing keywords combinations");

        $graph = $this->loader->load();

        $io->section("Prompts that cannot match with local repository");

        $listing = $this->recurBuildPrompt($graph, [], $input->getArgument('root-name'));
        foreach ($listing as $prompt) {
            $io->text(implode(' ', $prompt));
        }

        $filtered = array_filter($listing, function (array $prompt): bool {
            array_unique($prompt);
            return !count($this->repository->searchPicture(implode(' ', $prompt)));
        });

        $io->section("Stats on most common keywords in unmatched prompts");
        $stats = [];
        foreach ($filtered as $keywords) {
            foreach ($keywords as $word) {
                if (!key_exists($word, $stats)) {
                    $stats[$word] = 0;
                }
                $stats[$word]++;
            }
        }

        uasort($stats, function (int $a, int $b): int {
            return $b <=> $a;
        });

        foreach ($stats as $word => $nb) {
            $io->writeln("$word : $nb");
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
