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

    public function __construct(int $size)
    {
        parent::__construct();

        // root
        $root = $this->createElementNS(TileSvg::svgNS, 'svg');
        $root->setAttribute('viewBox', "0 0 $size $size");
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

}
