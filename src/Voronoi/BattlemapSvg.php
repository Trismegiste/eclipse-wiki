<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

/**
 * A vector battlemap
 */
class BattlemapSvg extends \DOMDocument
{

    public function __construct(string $version = "1.0", string $encoding = "")
    {
        parent::__construct($version, $encoding);
        
    }

    public function getGround(): \DOMElement
    {
        $xpath = new \DOMXPath($this);
        $xpath->registerNamespace('svg', TileSvg::svgNS);

        return $xpath->query('/svg:svg/svg:g[@id="ground"]')->item(0);
    }

}
