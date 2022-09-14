<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use App\Entity\Shape;
use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * Config for HexaMap
 */
class MapConfig implements Persistable
{

    use PersistableImpl;

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
    public array $tileWeight = [];       // weights of cluster tiles
    public array $tileMinCount = [];
    public array $tilePopulation = [];   // info on how to populate any tile (clusters and other tiles)

}
