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
        $randomSeed = 666;
        $size = 70;
        $avgTilePerRoom = 20;
        $minRoomSize = 13;
        $maxNeighbour = 5;

        $map = new HexaMap($size);

        $battlemap = new BattlemapSvg($size);
        foreach (['default', 'eastwall', 'eastdoor'] as $filename) {
            $svg = new TileSvg();
            $svg->load($this->getParameter('twig.default_path') . "/hex/tile/$filename.svg");
            $battlemap->appendTile($svg);
        }

        $draw = new \App\Voronoi\MapDrawer($map);

        $cell = new HexaCell();
        $cell->uid = 100;
        $draw->plantRandomSeed($cell, $avgTilePerRoom);

        if (false) {
            $street = new HexaCell();
            $street->uid = 6666666;
            $street->growable = false;

            for ($x = 0; $x < $size; $x++) {
                $map->setCell([$x, $size / 2], clone $street);
                $map->setCell([$x, $size / 2 + 1], clone $street);
            }

            for ($x = 0; $x < $size; $x += $size / 7) {
                for ($y = 0; $y < $size; $y++) {
                    $map->setCell([$x, $y], clone $street);
                }
            }
        }

        $hallway = new HexaCell();
        $hallway->uid = 10;
        $hallway->growable=false;
        $draw->drawHorizontalLine($hallway, 3);

        $filling = new HexaCell();
        $filling->uid = 0;
        $filling->growable = false;
        $draw->drawCircleContainer($filling);

        while ($map->iterateNeighbourhood()) {
            // nothing
        }

        $map->erodeWith($hallway, $minRoomSize, $maxNeighbour);

        $map->wallProcessing();

        $map->dump($battlemap);
        return $this->render('hex/generate.html.twig', ['map' => $battlemap]);
    }

}
