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
            ->addArgument('size', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $size = $input->getArgument('size');
        $pkTileSet = $input->getArgument('tileset');

        $fac = new \App\Entity\Wfc\Factory();
        $arrang = $this->repository->load($pkTileSet);

        $base = $fac->buildEigenTileBase($arrang);
        $wf = $fac->buildWaveFunction($size, $base);
        $wf->getCell([3, 0])->tileSuperposition = [$base[0], $base[2], $base[1], $base[7]];
        $wf->getCell([3, 4])->tileSuperposition = [$base[0], $base[2], $base[5]];
        $wf->getCell([6, 6])->tileSuperposition = [$base[0], $base[2], $base[5]];

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $cell = $wf->getCell([$x, $y]);
                // $output->write(sprintf("%08b ", $cell->tileMask));
                $output->write(sprintf("%d ", $cell->getEntropy()));
            }
            $output->writeln('');
        }

        var_dump($wf->findLowerEntropyCoordinates());

        return self::SUCCESS;
    }

}
