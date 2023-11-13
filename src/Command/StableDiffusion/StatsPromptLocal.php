<?php

/*
 * Eclipse Wiki
 */

namespace App\Command\StableDiffusion;

use App\Repository\CreationGraphProvider;
use App\Service\StableDiffusion\LocalRepository;
use App\Service\StableDiffusion\PictureInfo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Stats on local invokeAI repository
 */
#[AsCommand(name: 'sd:local:stats')]
class StatsPromptLocal extends Command
{

    const ignoredWord = ['a', 'with', 'in', 'the', 'of', 'to', 'and'];

    public function __construct(protected LocalRepository $repository, protected CreationGraphProvider $loader)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Stats on local repository of InvokeAI pictures");
        $iter = $this->repository->searchPicture('');
        $stats = [];
        $graphKeyword = $this->getAllKeywordFromGraph();
        $io->progressStart(count($iter));
        foreach ($iter as $picture) {
            $keywords = PictureInfo::extractCleanKeywords($picture->prompt);
            foreach ($keywords as $word) {
                if (!key_exists($word, $stats)) {
                    $stats[$word] = 0;
                }
                $stats[$word]++;
            }
            $io->progressAdvance();
        }
        $io->progressFinish();

        uasort($stats, function ($a, $b) {
            return $b <=> $a;
        });

        foreach ($stats as $word => $counter) {
            if (!in_array($word, self::ignoredWord)) {
                $color = in_array($word, $graphKeyword) ? 'green' : 'white';
                $io->text("<fg=$color>$word : $counter</>");
            }
        }

        return self::SUCCESS;
    }

    protected function getAllKeywordFromGraph(): array
    {
        $graph = $this->loader->load();
        $root = $graph->getNodeByName('root');
        $keywordPerLevel = $graph->accumulatePromptKeywordPerDistance($root);

        return array_merge(...$keywordPerLevel);
    }

}
