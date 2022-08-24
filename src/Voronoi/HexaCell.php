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

    public string $template = 'default';  // for use tag (color, pattern, textures...)
    public int $uid;  // to differentiate rooms
    public array $wall = [false, false, false, false, false, false]; // CCW, from EAST (0°) to SOUTHEAST (300°)
    public array $door = [false, false, false, false, false, false]; // CCW, from EAST (0°) to SOUTHEAST (300°)
    public bool $growable = true; // for voronoi algo
    public bool $npc = false;  // is there a npc

    public function dumpAt(BattlemapSvg $doc, float $cx, float $y): void
    {
        // Ground layer
        $item = $doc->createElementNS(TileSvg::svgNS, 'use');
        $item->setAttribute('x', $cx);
        $item->setAttribute('y', $y);
        $item->setAttribute('href', '#' . $this->template);
        // color
        $hue = ($this->uid % 20) * 18;
        $sat = ($this->uid % 2) ? '100%' : '70%';
        $item->setAttribute('fill', "hsl($hue,$sat,50%)");

        $title = $doc->createElementNS(TileSvg::svgNS, 'title');
        $title->textContent = 'room-' . $this->uid;
        $item->appendChild($title);

        $doc->getGround()->appendChild($item);

        // Wall layer - Since wall are set on each two cells, no need to duplicate the rendering
        for ($direction = HexaCell::EAST; $direction < HexaCell::WEST; $direction++) {
            if ($this->wall[$direction]) {
                $item = $doc->createElementNS(TileSvg::svgNS, 'use');
                $item->setAttribute('href', '#eastwall');
                $angle = -60 * $direction;
                $item->setAttribute('transform', "translate($cx $y) rotate($angle)");
                $doc->getWall()->appendChild($item);
            }
        }

        // Door layer
        for ($direction = HexaCell::EAST; $direction <= HexaCell::SOUTHEAST; $direction++) {
            if ($this->door[$direction]) {
                $item = $doc->createElementNS(TileSvg::svgNS, 'use');
                $item->setAttribute('href', '#eastdoor');
                $angle = -60 * $direction;
                $item->setAttribute('transform', "translate($cx $y) rotate($angle)");
                $doc->getDoor()->appendChild($item);
            }
        }
    }

}
