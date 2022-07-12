<?php

/*
 * eclipse-wiki
 */

namespace App\Command;

use App\Repository\TileArrangementRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of Hexa
 *
 * @author trismegiste
 */
class Hexa extends Command
{

    protected static $defaultName = 'hexa:map';
    protected $repository;

    public function __construct(TileArrangementRepository $repo)
    {
        $this->repository = $repo;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
                ->setDescription('Test Hexa')
                ->addArgument('tileset', InputArgument::REQUIRED)
                ->addArgument('size', InputArgument::REQUIRED)
                ->addArgument('iter', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $size = $input->getArgument('size');
        $maxIter = $input->getArgument('iter');
        $pkTileSet = $input->getArgument('tileset');

        $fac = new \App\Entity\Wfc\Factory();
        $arrang = $this->repository->load($pkTileSet);

        $base = $fac->buildEigenTileBase($arrang);
        $wf = $fac->buildWaveFunction($size, $base);

        $this->printWave($wf, $output);

        for ($iter = 0; $iter < $maxIter; $iter++) {
            $wf->iterate();
            $output->writeln('');
            $this->printWave($wf, $output);
        }

//        $output->writeln('');
//        for ($y = 0; $y < $size; $y++) {
//            for ($x = 0; $x < $size; $x++) {
//                $output->write(sprintf("%d ", count($wf->getNeighbourCoordinates([$x, $y]))));
//            }
//            $output->writeln('');
//        }

        return self::SUCCESS;
    }

    protected function printWave(\App\Entity\Wfc\WaveFunction $wf, OutputInterface $output): void
    {
        $size = $wf->getSize();
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $cell = $wf->getCell([$x, $y]);
                $output->write(sprintf("%d ", $cell->getEntropy()));
            }
            $output->writeln('');
        }
    }

}
