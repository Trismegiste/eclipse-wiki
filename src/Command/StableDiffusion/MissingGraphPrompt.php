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
        $root = $graph->getNodeByName($input->getArgument('root-name'));
        $dump = array_filter($graph->accumulatePromptKeywordPerDistance($root), function(array $keywords):bool {return count($keywords);});

        $io->section("Prompts that cannot match with local repository");
	$combi = $this->recurBuildPrompt('', $dump);
	$filtered = array_filter($this->recurBuildPrompt('', $dump), function(string $prompt):bool {
                    return !count($this->repository->searchPicture($prompt));
		});

	$io->text($filtered);

        $io->section("Stats on most common keywords in unmatched prompts");
	$stats = [];
	foreach($filtered as $prompt) {
	    $keywords = explode(' ', $prompt);
	    foreach($keywords as $word) {
		if (!key_exists($word, $stats)) {
		     $stats[$word] = 0;
                }
		$stats[$word]++;
            }
	}

	uasort($stats, function(int $a, int $b):int { return $b <=> $a; });

	foreach($stats as $word => $nb) {
	    $io->writeln("$word : $nb");
	}

	return 0;
    }

    protected function recurBuildPrompt(string $prefix, array $lastDigit): array {
	$result = [$prefix];
        $current = array_shift($lastDigit);

	if (is_null($current)) {
	    return $result;
 	}

        foreach($current as $keyword) {
            $children = $this->recurBuildPrompt((strlen($prefix) ? $prefix . ' ' : '') . $keyword, $lastDigit);
            foreach($children as $child) {
		$result[] = $child;
	    }
        }

        return $result;
    }
}
