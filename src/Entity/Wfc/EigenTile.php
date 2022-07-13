<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Wfc;

/**
 * Like a eigenvalue but for a tile
 */
class EigenTile
{

    protected $template;  // the filename of the tile
    protected $rotation;  // the rotation of the file (in degrees)
    public $neighbourList = [];  // the 6 neighbour lists of EigenTile from HexagonalTile::EAST to HexagonalTile::SOUTHEAST

    public function __construct(string $templateName, int $rotation = 0)
    {
        $this->template = $templateName;
        $this->rotation = $rotation;
    }

    public function getUniqueId(): string
    {
        return $this->template . '-' . $this->rotation;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getRotation(): int
    {
        return $this->rotation;
    }

}
