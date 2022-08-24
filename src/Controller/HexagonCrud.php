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
        $size = 50;
        $map = new HexaMap($size);

        $battlemap = new BattlemapSvg($size);
        foreach (['default', 'eastwall'] as $filename) {
            $svg = new TileSvg();
            $svg->load($this->getParameter('kernel.project_dir') . "/templates/hex/tile/$filename.svg");
            $battlemap->appendTile($svg);
        }

        for ($k = 0; $k < 300; $k++) {
            $cell = new HexaCell();
            $cell->uid = $k;
            $map->setCell([rand(0, $size - 1), rand(0, $size - 1)], $cell);
        }

        while ($map->iterateNeighbourhood()) {
            // nothing
        }
        $map->wallProcessing();

        $map->dump($battlemap);
        return $this->render('hex/generate.html.twig', ['map' => $battlemap]);
    }

}
