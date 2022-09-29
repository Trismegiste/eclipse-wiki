<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

/**
 * Provider of Pictogram (SVG fragment for battlemap)
 */
class PictoProvider
{
    protected $pictoFolder;

    public function __construct(string $basepath)
    {
        $this->pictoFolder = $basepath;
    }

    public function findAll(): array
    {
	$finder = new Finder();
        $finder->in($this->pictoFolder)->files()->name('*.svg');

        $listing = [];
	foreach ($finder as $picto) {
	    $key = $picto->getBasename('.svg')
            $listing[ucfirst($key)] = $key;
        }

        return $listing;
    }

    public function getSvg(string $key): string
    {
        $doc = new \DOMDocument();
        $doc->load($this->pictoFolder . "/$key.svg");
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('svg', \App\Voronoi\TileSvg::svgNS);
        $extract = $xpath->query('/svg:svg/svg:g')->item(0);

        return $extract->C14N();
    }

}
