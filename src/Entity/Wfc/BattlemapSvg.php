<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Wfc;

/**
 * Description of BattlemapSvg
 *
 * @author trismegiste
 */
class BattlemapSvg extends \DOMDocument
{

    public function getGround(): \DOMElement
    {
        $xpath = new \DOMXPath($this);
        $xpath->registerNamespace('svg', TileSvg::svgNS);

        return $xpath->query('/svg:svg/svg:g[@id="ground"]')->item(0);
    }

}
