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

    public function printAt(BattlemapSvg $doc, string $txt, float $x, float $y): void
    {
        $item = $doc->createElementNS(TileSvg::svgNS, 'text');
        $item->setAttribute('x', $x);
        $item->setAttribute('y', $y + 0.15);
        $item->setAttribute('font-size', 0.3);
        $item->textContent = $txt;
        $item->setAttribute('text-anchor', 'middle');

        $doc->getLegend()->appendChild($item);
    }

}
