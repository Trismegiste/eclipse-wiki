<?php

/*
 * Eclipse Wiki
 */

namespace App\Command\StableDiffusion;

use App\Service\StableDiffusion\LocalRepository;
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

    public function __construct(protected LocalRepository $repository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Stats on local repository of InvokeAI pictures");
        $iter = $this->repository->searchPicture('');
        $stats = [];
        $io->progressStart(count($iter));
        foreach ($iter as $picture) {
            $keywords = \App\Service\StableDiffusion\PictureInfo::extractCleanKeywords($picture->prompt);
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
            $io->text("$word : $counter");
        }

        return self::SUCCESS;
    }

}
