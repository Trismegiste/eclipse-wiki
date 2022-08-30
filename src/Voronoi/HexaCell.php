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

    public string $template;  // for use tag (color, pattern, textures...)
    public int $uid;  // to differentiate rooms
    public bool $growable; // for voronoi algo
    public array $wall = [false, false, false, false, false, false]; // CCW, from EAST (0°) to SOUTHEAST (300°)
    public array $door = [false, false, false, false, false, false]; // CCW, from EAST (0°) to SOUTHEAST (300°)
    public bool $npc = false;  // is there a npc

    public function __construct(int $uid, string $template = 'default', bool $growable = true)
    {
        $this->uid = $uid;
        $this->template = $template;
        $this->growable = $growable;
    }

    /**
     * Modifies the battlemap SVG to append this cell. The coordinates of the cell is calculated and given by the battlemap
     * @param BattlemapSvg $doc
     * @param float $cx abscissa
     * @param float $y ordinate
     * @return void
     */
    public function dumpAt(BattlemapSvg $doc, float $cx, float $y): void
    {
        // Ground layer
        $item = $doc->createUse($this->template);
        $item->setAttribute('x', $cx);
        $item->setAttribute('y', $y);

        $title = $doc->createElementNS(TileSvg::svgNS, 'title');
        $title->textContent = 'room-' . $this->uid;
        $item->appendChild($title);

        $doc->getGround()->appendChild($item);

        // Wall layer - Since wall are set on each two cells, no need to duplicate the rendering
        for ($direction = HexaCell::EAST; $direction < HexaCell::WEST; $direction++) {
            if ($this->wall[$direction]) {
                $item = $doc->createUse('eastwall');
                $angle = -60 * $direction;
                $item->setAttribute('transform', "translate($cx $y) rotate($angle)");
                $doc->getWall()->appendChild($item);
            }
        }

        // Door layer
        for ($direction = HexaCell::EAST; $direction <= HexaCell::SOUTHEAST; $direction++) {
            if ($this->door[$direction]) {
                $item = $doc->createUse('eastdoor');
                $angle = -60 * $direction;
                $item->setAttribute('transform', "translate($cx $y) rotate($angle)");
                $doc->getDoor()->appendChild($item);
            }
        }
    }

    public function dumpGround(float $cx, float $y): void
    {
        echo "<use xlink:href=\"#{$this->template}\" x=\"$cx\" y=\"$y\">";
        echo "<title>room-{$this->uid}</title>";
        echo "</use>\n";
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

}
