<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use App\Entity\MapConfig;
use App\Repository\TileProvider;
use App\Service\Storage;
use RuntimeException;

/**
 * Service for creating a HexaMap
 */
class MapBuilder
{

    protected TileProvider $provider;
    protected Storage $storage;
    protected MapDrawer $drawer;

    public function __construct(TileProvider $provider, Storage $storage, MapDrawer $draw)
    {
        $this->provider = $provider;
        $this->storage = $storage;
        $this->drawer = $draw;
    }

    /**
     * Builds the battlemap with all parameters from MapConfig
     * @param MapConfig $config
     * @return HexaMap
     * @throws RuntimeException
     */
    public function create(MapConfig $config): HexaMap
    {
        $map = new HexaMap($config->side);

        srand($config->seed);
        $this->drawer->setMap($map);

        $this->drawer->plantRandomSeed(new HexaCell(HexaCell::CLUSTER_UID, 'cluster'), $config->avgTilePerRoom);

        $hallway = new HexaCell(HexaCell::SPACING_UID, 'default', false);

        if ($config->horizontalLines > 0) {
            $this->drawer->horizontalCross($hallway, $config->horizontalLines, $config->doubleHorizontal);
        }
        if ($config->verticalLines > 0) {
            $this->drawer->verticalCross($hallway, $config->verticalLines, $config->doubleVertical);
        }

        $config->container->draw($this->drawer);

        $current = $map->iterateNeighbourhood();
        // we iterates as long as the count of empty cells is shrinking on each iteration
        do {
            $lastEmpty = $current;
            $current = $map->iterateNeighbourhood();
        } while ($current < $lastEmpty);

        // if there are still empty cells, stops generation and throw exception
        if ($current > 0) {
            throw new RuntimeException("Cannot fill $current remaining cells with Voronoi iterations");
        }

        if ($config->erosion) {
            $map->erodeWith($hallway, $config->erodingMinRoomSize, $config->erodingMaxNeighbour);
        }

        $map->wallProcessing();
        $map->texturing($config->tileWeight, $config->minClusterPerTile);
        $map->populating($config->tilePopulation);

        return $map;
    }

}
