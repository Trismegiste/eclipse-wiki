<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Wfc;

/**
 * The value of the wave function at a precise hexagon
 */
class WaveCell
{

    public $tileMask; // mask (probability) for each EigenTile
    
    // @todo Change to an array of [null or EigenTile] at a fixed position (from the base)

    public function getEntropy(): int
    {
        return substr_count(decbin($this->tileMask), '1');
    }

}
