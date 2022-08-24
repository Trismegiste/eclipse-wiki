<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

/**
 * A tile extracted from a SVG
 * The tile must be contained in a group (<g> tag) with an id equals to the filename (without extension)
 */
class TileSvg extends \DOMDocument
{

    const svgNS = 'http://www.w3.org/2000/svg';

    protected $key;

    public function load($filename, $options = 0)
    {
        $this->key = pathinfo($filename, PATHINFO_FILENAME);

        return parent::load($filename, $options);
    }

    public function getTile(): \DOMElement
    {
        $xpath = new \DOMXPath($this);
        $xpath->registerNamespace('svg', self::svgNS);

        return $xpath->query('/svg:svg/svg:g[@id="' . $this->key . '"]')->item(0);
    }

    public function getKey(): string
    {
        return $this->key;
    }

}
