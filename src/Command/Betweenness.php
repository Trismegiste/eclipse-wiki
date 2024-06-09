<?php

/*
 * Eclipse Wiki
 */

namespace App\Command;

use App\Repository\VertexRepository;
use App\Service\DigraphExplore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Description of Betweenness
 *
 * @author florent
 */
#[AsCommand(name: 'graph:between')]
class Betweenness extends Command
{

    public function __construct(protected VertexRepository $repository,
            protected DigraphExplore $explorer,
            protected \App\Service\AlgorithmClient $algorithm
    )
    {

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $graph = $this->repository->loadGraph();
        $partition = $this->explorer->getPartitionByDistanceFromCategory($graph, 'timeline')['La porte de la Nuit'];
        $pk2idx = array_flip(array_keys($graph->vertex));
        $matrix = [];
        foreach ($partition as $row => $source) {
            foreach ($partition as $col => $target) {
                $s = $pk2idx[$source->pk];
                $t = $pk2idx[$target->pk];
                $matrix[$row][$col] = $graph->adjacency[$s][$t] || $graph->adjacency[$t][$s];
            }
        }

        $between = $this->algorithm->brandesCentrality($matrix);

        arsort($between);
        foreach ($between as $idx => $weight) {
            $vertex = $partition[$idx];
            //    if ($vertex->category === 'transhuman') {
            $output->writeln(sprintf("%s %f", $vertex->title, $weight));
            //    }
        }

        return self::SUCCESS;
    }

}
