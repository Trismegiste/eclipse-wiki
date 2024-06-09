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
        $timeline = $this->repository->findByTitle('Mentrats virtuels');
        $perCategory = $this->explorer->getVertexSortedByCentrality($timeline);

        foreach ($perCategory as $category => $lidting) {
            $output->writeln("==$category==");
            foreach ($lidting as $vertex) {
                $output->writeln($vertex->title);
            }
            $output->writeln('');
        }

        return self::SUCCESS;
    }

}
