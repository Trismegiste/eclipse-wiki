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

    protected DOMElement $defs;
    protected DOMElement $ground;
    protected DOMElement $wall;
    protected DOMElement $door;

    public function __construct(int $size)
    {
        parent::__construct();

        // root
        $width = $size * (2 / sqrt(3)) + 1;  // because of the included rectangle in a hexagon
        $height = $size + 1;
        $root = $this->createElementNS(TileSvg::svgNS, 'svg');
        $root->setAttribute('viewBox', "-1 -1 $width $height");
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
    }

    public function getGround(): DOMElement
    {
        return $this->ground;
    }

    public function appendTile(TileSvg $svg): void
    {
        $item = $svg->getTile();
        $imported = $this->importNode($item, true);
        $this->defs->appendChild($imported);
    }

    public function getWall(): DOMElement
    {
        return $this->wall;
    }

    public function getDoor(): DOMElement
    {
        return $this->door;
    }

}
