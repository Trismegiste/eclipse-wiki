<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use App\Voronoi\TileSvg;
use DOMDocument;
use DOMElement;

/**
 * A vector battlemap
 */
class BattlemapSvg extends DOMDocument
{

    const defaultSizeForWeb = 1200;

    protected DOMElement $defs;
    protected DOMElement $ground;
    protected DOMElement $wall;
    protected DOMElement $door;
    protected DOMElement $legend;
    protected DOMElement $fogOfWar;

    public function __construct(int $size, bool $withSvgSize = false)
    {
        parent::__construct();
        $cos30 = sqrt(3) / 2.0;

        // root
        $width = $size / $cos30 + 1;  // because of the included rectangle in a hexagon
        $height = $size + 1;
        $root = $this->createElementNS(TileSvg::svgNS, 'svg');
        $root->setAttribute('viewBox', "-1 -1 $width $height");
        // if the SVG needs width and height (in pixels)
        if ($withSvgSize) {
            $root->setAttribute('height', (int) floor(self::defaultSizeForWeb * $cos30));
            $root->setAttribute('width', self::defaultSizeForWeb);
        }
        $this->appendChild($root);

        // svg defs
        $this->defs = $this->createElementNS(TileSvg::svgNS, 'defs');
        $root->appendChild($this->defs);

        // ground layer
        $this->ground = $this->createElementNS(TileSvg::svgNS, 'g');
        $this->ground->setAttribute('id', 'ground');
        $root->appendChild($this->ground);

        // wall layer
        $this->wall = $this->createElementNS(TileSvg::svgNS, 'g');
        $this->wall->setAttribute('id', 'wall');
        $root->appendChild($this->wall);

        // door layer
        $this->door = $this->createElementNS(TileSvg::svgNS, 'g');
        $this->door->setAttribute('id', 'door');
        $root->appendChild($this->door);

        // legend layer
        $this->legend = $this->createElementNS(TileSvg::svgNS, 'g');
        $this->legend->setAttribute('id', 'legend');
        $root->appendChild($this->legend);

        // Fog of war layer
        $this->fogOfWar = $this->createElementNS(TileSvg::svgNS, 'g');
        $this->fogOfWar->setAttribute('id', 'fogofwar');
        $root->appendChild($this->fogOfWar);
    }

    /**
     * Gets the ground layer (a.k.a a svg <g> tag)
     * @return DOMElement
     */
    public function getGround(): DOMElement
    {
        return $this->ground;
    }

    /**
     * Appends a tile in the definitions of this SVG
     * @param TileSvg $svg
     * @return void
     */
    public function appendTile(TileSvg $svg): void
    {
        $item = $svg->getTile();
        $imported = $this->importNode($item, true);
        $this->defs->appendChild($imported);
    }

    /**
     * Gets the walls layer
     * @return DOMElement
     */
    public function getWall(): DOMElement
    {
        return $this->wall;
    }

    /**
     * Gets the doors layer
     * @return DOMElement
     */
    public function getDoor(): DOMElement
    {
        return $this->door;
    }

    /**
     * Gets the legend layer
     * @return DOMElement
     */
    public function getLegend(): DOMElement
    {
        return $this->legend;
    }

    /**
     * Gets the fog of war layer
     * @return DOMElement
     */
    public function getFogOfWar(): DOMElement
    {
        return $this->fogOfWar;
    }

}
