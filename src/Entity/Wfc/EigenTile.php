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
    public $neighborMask = [];  // the 6 neighbors masks

}
