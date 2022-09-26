<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

/**
 * A hexagonal cell
 */
class HexaCell implements BattlemapItem
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

    public function __construct(int $uid, string $template = 'default', bool $growable = true)
    {
        $this->uid = $uid;
        $this->template = $template;
        $this->growable = $growable;
    }

    public function dumpGround(float $cx, float $y): void
    {
        echo "<use xlink:href=\"#{$this->template}\" x=\"$cx\" y=\"$y\"/>\n";
    }

    public function dumpWall(float $cx, float $y): void
    {
        // Since walls are set on each of the two adjacent cells, we render half of all walls
        for ($direction = HexaCell::EAST; $direction < HexaCell::WEST; $direction++) {
            if ($this->wall[$direction]) {
                $angle = -60 * $direction;
                echo "<use xlink:href=\"#eastwall\" transform=\"translate($cx $y) rotate($angle)\"/>\n";
            }
        }
    }

    public function dumpDoor(float $cx, float $y): void
    {
        for ($direction = HexaCell::EAST; $direction <= HexaCell::SOUTHEAST; $direction++) {
            if ($this->door[$direction]) {
                $angle = -60 * $direction;
                echo "<use xlink:href=\"#eastdoor\" transform=\"translate($cx $y) rotate($angle)\"/>\n";
            }
        }
    }

    public function dumpLegend(string $txt, float $x, float $y): void
    {
        $fontSize = 0.3;
        $y += $fontSize / 2;
        echo "<text font-size=\"$fontSize\" x=\"$x\" y=\"$y\" text-anchor=\"middle\">";
        echo $txt;
        echo "</text>\n";
    }

    public function dumpNpc(float $x, float $y): void
    {
        if (!is_null($this->npc)) {
            // "-0.4" is a bugfix for svg.draggable.js
            printf('<use xlink:href="#%s" x="%f" y="%f" data-npc-title="%s"/>', basename($this->npc->picture, '.png'),
                      $x - 0.4, $y - 0.4, $this->npc->label);
        }
    }

}
