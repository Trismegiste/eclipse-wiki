<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use App\Voronoi\Shape;

/**
 * Config entity for HexaMap
 */
class MapConfig extends Vertex
{

    public int $seed;
    public int $side;
    public int $avgTilePerRoom;
    public bool $erosion = false;
    public ?int $erodingMinRoomSize;
    public ?int $erodingMaxNeighbour;
    public Shape\Strategy $container;
    public int $horizontalLines = 0;
    public bool $doubleHorizontal = false;
    public int $verticalLines = 0;
    public bool $doubleVertical = false;

}
