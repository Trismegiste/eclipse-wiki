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

    public function __construct(protected VertexRepository $repository, protected DigraphExplore $explorer)
    {

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $edge = tmpfile();
        $result = tmpfile();

        $graph = $this->repository->loadGraph();
        $partition = $this->explorer->getPartitionByDistanceFromCategory($graph, 'timeline')['La porte de la Nuit'];
        $pk2idx = array_flip(array_keys($graph->vertex));
        $matrix = [];
        foreach ($partition as $row => $source) {
            foreach ($partition as $col => $target) {
                $matrix[$row][$col] = $graph->adjacency[$pk2idx[$source->pk]][$pk2idx[$target->pk]];
            }
        }

        foreach ($matrix as $row => $vector) {
            foreach ($vector as $col => $flag) {
                if ($flag || $matrix[$col][$row]) {
                    fprintf($edge, "%d %d\n", $row, $col);
                }
            }
        }
        $brandes = new Process([
            'brandes',
            16,
            stream_get_meta_data($edge)['uri'],
            stream_get_meta_data($result)['uri']
        ]);
        $brandes->mustRun();
        fclose($edge);

        $between = [];
        while ($row = fscanf($result, '%d %f')) {
            list($idx, $centrality) = $row;
            $between[$idx] = $centrality;
        }
        fclose($result);

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
