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

    const VOID_UID = 0;
    const HALLWAY_UID = 10;
    const ROOM_UID = 100;

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

        $draw->plantRandomSeed(new HexaCell(self::ROOM_UID, 'room'), $config->avgTilePerRoom);

        $hallway = new HexaCell(self::HALLWAY_UID, 'default', false);

        if ($config->horizontalLines > 0) {
            $draw->horizontalCross($hallway, $config->horizontalLines, $config->doubleHorizontal);
        }
        if ($config->verticalLines > 0) {
            $draw->verticalCross($hallway, $config->verticalLines, $config->doubleVertical);
        }

        $config->container->draw($draw);

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
