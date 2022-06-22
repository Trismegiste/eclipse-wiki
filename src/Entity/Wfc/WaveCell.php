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
    public $updated;  // don't come back to this hexagon, it has been already collapsed

}
