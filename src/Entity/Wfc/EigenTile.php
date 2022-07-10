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

    public $filename;  // the filename of the tile
    public $rotation;  // the rotation of the file (in degrees)
    public $neighbourList = [];  // the 6 neighbour lists of EigenTile from HexagonalTile::EAST to HexagonalTile::SOUTHEAST

}
