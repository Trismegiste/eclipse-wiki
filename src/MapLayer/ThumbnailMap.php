<?php

/*
 * eclipse-wiki
 */

namespace App\MapLayer;

/**
 * 
 */
class ThumbnailMap implements \Stringable
{

    protected $mapInfo;

    public function __construct(\Symfony\Component\Finder\SplFileInfo $svg)
    {
        $this->mapInfo = $svg;

        $doc = new \DOMDocument();
        $doc->loadXML($svg->getContents());

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('svg', 'http://www.w3.org/2000/svg');
        $title = trim($xpath->query('/svg:svg/svg:title')->item(0)->nodeValue);
        $desc = json_decode($xpath->query('/svg:svg/svg:desc')->item(0)->nodeValue, true);

        $content = $xpath->query('/svg:svg/svg:g[@class="building"]')->item(0);
    }

    public function __toString(): string
    {
        
    }

    public function getTitle(): string
    {
        
    }

}
