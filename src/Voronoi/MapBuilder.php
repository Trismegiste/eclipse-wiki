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
        foreach (['default', 'eastwall', 'eastdoor', 'room', 'void'] as $filename) {
            $svg = new TileSvg();
            $svg->load("{$this->tilePath}/$filename.svg");
            $battlemap->appendTile($svg);
        }

        srand($config->seed);
        $draw = new MapDrawer($map);

        $draw->plantRandomSeed(new HexaCell(100, 'room'), $config->avgTilePerRoom);

        $hallway = new HexaCell(10, 'default', false);

        if ($config->horizontalLines > 0) {
            $draw->drawHorizontalLine($hallway, $config->horizontalLines, $config->doubleHorizontal);
        }
        if ($config->verticalLines > 0) {
            $draw->drawVerticalLine($hallway, $config->verticalLines, $config->doubleVertical);
        }

        if (!empty($config->container)) {
            $filling = new HexaCell(0, 'void', false);
            $draw->drawCircleContainer($filling);
        }

        while ($map->iterateNeighbourhood()) {
            // nothing
        }

        if ($config->erosion) {
            $map->erodeWith($hallway, $config->erodingMinRoomSize, $config->erodingMaxNeighbour);
        }

        $map->wallProcessing();
        $map->dump($battlemap);

        return $battlemap;
    }

}
