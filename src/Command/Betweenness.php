<?php

/*
 * Eclipse Wiki
 */

namespace App\Command;

use App\Repository\VertexRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of Betweenness
 *
 * @author florent
 */
#[AsCommand(name: 'graph:between')]
class Betweenness extends Command
{

    public function __construct(protected VertexRepository $repository)
    {

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $edge = tmpfile();
        $result = tmpfile();

        $graph = $this->repository->loadGraph();
        foreach ($graph->adjacency as $row => $vector) {
      //      $output->writeln(sprintf('%d %s', $row, $graph->getVertexByIndex($row)->title));
            foreach ($vector as $col => $flag) {
                if ($flag || $graph->adjacency[$col][$row]) {
                    fprintf($edge, "%d %d\n", $row, $col);
                }
            }
        }
        $brandes = new \Symfony\Component\Process\Process([
            'bin/brandes',
            16,
            stream_get_meta_data($edge)['uri'],
            stream_get_meta_data($result)['uri']
        ]);
        $brandes->mustRun();
    //    echo file_get_contents(stream_get_meta_data($edge)['uri']) . PHP_EOL;
     //   echo file_get_contents(stream_get_meta_data($result)['uri']) . PHP_EOL;
        fclose($edge);

        $between = [];
        while ($row = fscanf($result, '%d %f')) {
            list($idx, $centrality) = $row;
            $between[$idx] = $centrality;
        }
        fclose($result);

        arsort($between);
        foreach ($between as $idx => $weight) {
            $vertex = $graph->getVertexByIndex($idx);
            if ($vertex->category === 'transhuman') {
                $output->writeln(sprintf("%s %f", $vertex->title, $weight));
            }
        }

        return self::SUCCESS;
    }

}
