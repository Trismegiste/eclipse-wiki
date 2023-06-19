<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Repository\VertexRepository;
use App\Service\Storage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\SplFileInfo;
use App\Entity\Timeline;

/**
 * Migrate database
 */
#[AsCommand(name: 'db:migrate')]
class Migration extends Command
{

    protected $repo;

    public function __construct(VertexRepository $repo)
    {
        parent::__construct();
        $this->repo = $repo;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Migration");

        $iter = $this->repo->findByClass(Timeline::class);

        foreach ($iter as $vertex) {
            $io->writeln(">>>>>>>>>>> Migrate '" . $vertex->getTitle() . "'");
            $content = $vertex->getContent();
            $vertex->elevatorPitch = $content;

            $timeline = [];
            $rows = explode("\n", $content);
            $startRecording = false;
            foreach ($rows as $row) {
                if (str_starts_with($row, '==')) {
                    if (preg_match('#^==([^=]+)==\s*$#', $row, $extract)) {
                        if ('Timeline' == $extract[1]) {
                            $startRecording = true;
                        } else {
                            $startRecording = false;
                        }
                    }
                }

                if ($startRecording && str_starts_with($row, '*')) {
                    $timeline[] = str_replace(['{{task|', '|checked', '}}', '<strike>', '</strike>'], ['', '', '', '', ''], trim(substr($row, 1)));
                }
            }
            $tree = new \App\Entity\PlotNode('Root');
            foreach ($timeline as $node) {
                $tree->nodes[] = new \App\Entity\PlotNode($node);
            }
            $vertex->setTree($tree);
            var_dump($vertex->bsonSerialize());
        }
        $io->success('End of migration');

        return Command::SUCCESS;
    }

}
