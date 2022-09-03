<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use App\Entity\Shape;

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
    public array $tileWeight = ['room-A' => 1, 'room-B' => 1, 'cluster' => 3];
    public array $tileMinCount = ['room-C' => 3];

    // e0d89a
    // abe09a
    // 9ae0d9
    // 9aa8e0
    // e09ad3
    // e0a29a
}
