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

    /**
     * absolute pathname of the SVG
     * @var string
     */
    public $filename;

    /**
     * array of 6 strings naming the "type" of connections (free and arbitrary)
     * @var array
     */
    protected $anchor;

    /**
     * array of 6 booleans if one of the 6 rotations (from 0° to 300°) is enabled
     * @var array
     */
    protected $rotation;

    public function __construct()
    {
        $this->anchor = array_fill(self::EAST, self::SIDES, null);
        $this->rotation = [true, false, false, false, false, false];  // default : rotation of 0° is always available
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
