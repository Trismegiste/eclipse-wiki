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
    public $tileList; // an array of EigenTile
    public $dirty; // already computed
}
