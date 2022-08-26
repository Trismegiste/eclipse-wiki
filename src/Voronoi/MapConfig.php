<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use App\Entity\Vertex;

/**
 * Config entity for HexaMap
 */
class MapConfig extends Vertex
{

    public int $seed;
    public int $side;
    public int $avgTilePerRoom;
    public bool $erosion;
    public int $erodingMinRoomSize;
    public int $erodingMaxNeighbour;
    public ?string $container;
    public int $horizontalLines;
    public bool $doubleHorizontal;
    public int $verticalLines;
    public bool $doubleVertical;

}
