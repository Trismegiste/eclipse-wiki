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
    protected $title;
    protected $desc;
    protected $thumb;

    public function __construct(\Symfony\Component\Finder\SplFileInfo $svg)
    {
        $this->mapInfo = $svg;

        $doc = new \DOMDocument();
        $doc->loadXML($svg->getContents());

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('svg', 'http://www.w3.org/2000/svg');
        $this->title = trim($xpath->query('/svg:svg/svg:title')->item(0)->nodeValue);
        $this->desc = json_decode($xpath->query('/svg:svg/svg:desc')->item(0)->nodeValue, true);

        $content = $xpath->query('/svg:svg/svg:g[@class="building"]')->item(0);

        $this->thumb = new \DOMDocument();
        $root = $this->thumb->createElementNS('svg', 'svg');
        $root->setAttribute('viewBox', $doc->firstChild->attributes->getNamedItem('viewBox')->nodeValue);
        $root->setAttribute('width', 300);
        $root->setAttribute('height', 300);
        $building = $this->thumb->importNode($content, true);
        $root->appendChild($building);
        $this->thumb->appendChild($root);
        unset($xpath);
        unset($doc);
    }

    public function __toString(): string
    {
        return $this->thumb->saveXML();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

}
