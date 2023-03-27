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

    // direction
    const EAST = 0;
    const NORTHEAST = 1;
    const NORTHWEST = 2;
    const WEST = 3;
    const SOUTHWEST = 4;
    const SOUTHEAST = 5;
    // default UID by type
    const VOID_UID = 0;
    const SPACING_UID = 10;
    const CLUSTER_UID = 100;

    public string $template;  // for use tag (color, pattern, textures...)
    public int $uid;  // to differentiate rooms
    public bool $growable; // for voronoi algo
    public array $wall = [false, false, false, false, false, false]; // CCW, from EAST (0°) to SOUTHEAST (300°)
    public array $door = [false, false, false, false, false, false]; // CCW, from EAST (0°) to SOUTHEAST (300°)
    public ?\App\Entity\MapToken $npc = null;
    public ?string $legend = null;
    public ?string $pictogram = null;

    public function __construct(int $uid, string $template = 'default', bool $growable = true)
    {
        $this->uid = $uid;
        $this->template = $template;
        $this->growable = $growable;
    }

}
