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
        $size = 51;
        $avgTilePerRoom = 15;

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

        $draw = new \App\Voronoi\MapDrawer($map);
        $filling = new HexaCell();
        $filling->uid = 111111;
        $filling->growable = false;
        $draw->drawCircleContainer($filling);

        while ($map->iterateNeighbourhood()) {
            // nothing
        }

        $hallway = new HexaCell();
        $hallway->uid = 222222;
        $map->erodeWith($hallway);

        $map->wallProcessing();

        $map->dump($battlemap);
        return $this->render('hex/generate.html.twig', ['map' => $battlemap]);
    }

}
