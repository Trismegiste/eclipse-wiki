<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

/**
 * A hexagonal cell
 */
class HexaCell
{

    public string $template;  // for use tag (color, pattern, textures...)
    public int $uid;  // to differentiate rooms
    public array $wall = [false, false, false, false, false, false]; // CCW, from EAST (0°) to SOUTHEAST (300°)
    public array $door = [false, false, false, false, false, false]; // CCW, from EAST (0°) to SOUTHEAST (300°)
    public bool $growable = true; // for voronoi algo
    public bool $npc = false;  // is there a npc ?

}
