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

    const EAST = 0;
    const NORTHEAST = 1;
    const NORTHWEST = 2;
    const WEST = 3;
    const SOUTHWEST = 4;
    const SOUTHEAST = 5;

    public string $template = 'empty';  // for use tag (color, pattern, textures...)
    public int $uid;  // to differentiate rooms
    public array $wall = [false, false, false, false, false, false]; // CCW, from EAST (0°) to SOUTHEAST (300°)
    public array $door = [false, false, false, false, false, false]; // CCW, from EAST (0°) to SOUTHEAST (300°)
    public bool $growable = true; // for voronoi algo
    public bool $npc = false;  // is there a npc

}
