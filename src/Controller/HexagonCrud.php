<?php

/*
 * eclipse-wiki
 */

namespace App\Controller;

use App\Voronoi\BattlemapSvg;
use App\Voronoi\HexaCell;
use App\Voronoi\HexaMap;
use App\Voronoi\TileSvg;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CRUD controler for Hexagonal map
 */
class HexagonCrud extends AbstractController
{

    /**
     * @Route("/map/voronoi")
     */
    public function voronoi(): Response
    {
        // @todo => form
        $size = 40;
        $avgTilePerRoom = 12;

        $map = new HexaMap($size);

        $battlemap = new BattlemapSvg($size);
        foreach (['default', 'eastwall', 'eastdoor'] as $filename) {
            $svg = new TileSvg();
            $svg->load($this->getParameter('kernel.project_dir') . "/templates/hex/tile/$filename.svg");
            $battlemap->appendTile($svg);
        }

        for ($k = 0; $k < $size * $size / $avgTilePerRoom; $k++) {
            $cell = new HexaCell();
            $cell->uid = $k;
            $map->setCell([rand(0, $size - 1), rand(0, $size - 1)], $cell);
        }

        for ($x = 0; $x < $size; $x++) {
            $cell = new HexaCell();
            $cell->uid = 6666666;
            $cell->growable = false;
            $map->setCell([$x, $size / 2], $cell);
            $map->setCell([$x, $size / 2 + 1], clone $cell);
        }

        for ($x = 0; $x < $size; $x += $size / 7) {
            $cell = new HexaCell();
            $cell->uid = 6666666;
            $cell->growable = false;
            for ($y = 0; $y < $size; $y++) {
                $map->setCell([$x, $y], clone $cell);
            }
        }

        while ($map->iterateNeighbourhood()) {
            // nothing
        }
        $map->wallProcessing();

        $map->dump($battlemap);
        return $this->render('hex/generate.html.twig', ['map' => $battlemap]);
    }

}
