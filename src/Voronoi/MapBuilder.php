<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

/**
 * Service for creating a HexaMap
 */
class MapBuilder
{

    protected $tilePath;

    public function __construct(string $tilePath)
    {
        $this->tilePath = $tilePath;
    }

    public function create(MapConfig $config): BattlemapSvg
    {
        $map = new HexaMap($config->side);

        $battlemap = new BattlemapSvg($config->side);
        foreach (['default', 'eastwall', 'eastdoor'] as $filename) {
            $svg = new TileSvg();
            $svg->load("{$this->tilePath}/$filename.svg");
            $battlemap->appendTile($svg);
        }

        srand($config->seed);
        $draw = new MapDrawer($map);

        $cell = new HexaCell();
        $cell->uid = 100;
        $draw->plantRandomSeed($cell, $config->avgTilePerRoom);

        $hallway = new HexaCell();
        $hallway->uid = 10;
        $hallway->growable = false;
        $draw->drawHorizontalLine($hallway, 1, true);
        $draw->drawVerticalLine($hallway, 1, true);

        $filling = new HexaCell();
        $filling->uid = 0;
        $filling->growable = false;
        $draw->drawCircleContainer($filling);

        while ($map->iterateNeighbourhood()) {
            // nothing
        }

        $map->erodeWith($hallway, $config->erodingMinRoomSize, $config->erodingMaxNeighbour);

        $map->wallProcessing();

        $map->dump($battlemap);

        return $battlemap;
    }

}
