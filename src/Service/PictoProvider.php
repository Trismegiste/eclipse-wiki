<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use DOMDocument;
use Symfony\Component\Finder\Finder;

/**
 * Provider of Pictogram (SVG fragment for battlemap)
 */
class PictoProvider
{

    const textureSize = 100;

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
            $key = $picto->getBasename('.svg');
            $listing[ucfirst($key)] = $key;
        }

        return $listing;
    }

    public function getSvg(string $key): string
    {
        $doc = new DOMDocument();
        $doc->load($this->pictoFolder . "/$key.svg");
        $doc->documentElement->setAttribute('width', self::textureSize);
        $doc->documentElement->setAttribute('height', self::textureSize);

        return $doc->saveXML();
    }

}
