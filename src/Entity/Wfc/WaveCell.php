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

    public $tileSuperposition; // list of possible EigenTile

    public function getEntropy(): int
    {
        return count($this->tileSuperposition);
    }

}
