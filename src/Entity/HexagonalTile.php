<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * One hexagonal tile with its connections and generating rotations
 */
class HexagonalTile implements Persistable
{

    use PersistableImpl;

    const EAST = 0;
    const NORTHEAST = 1;
    const NORTHWEST = 2;
    const WEST = 3;
    const SOUTHWEST = 4;
    const SOUTHEAST = 5;
    const SIDES = 6;

    public $filename;
    protected $anchor;
    protected $rotation;

    public function __construct()
    {
        $this->anchor = array_fill(self::EAST, self::SIDES, null);
        $this->rotation = [true, false, false, false, false, false];  // default : rotation of 0° is available
    }

    public function getAnchor(): array
    {
        return $this->anchor;
    }

    public function setAnchor(array $tab): void
    {
        $this->anchor = $tab;
    }

    public function getRotation(): array
    {
        return $this->rotation;
    }

    public function setRotation(array $tab): void
    {
        $this->rotation = $tab;
    }

}
