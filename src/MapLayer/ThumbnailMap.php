<?php

/*
 * eclipse-wiki
 */

namespace App\MapLayer;

use App\Controller\MapCrud;
use DOMDocument;
use DOMXPath;
use SplFileInfo;
use Stringable;

/**
 * Bridge for SVG
 */
class ThumbnailMap implements Stringable
{

    const svgNS = 'http://www.w3.org/2000/svg';

    protected $title;
    protected $desc;
    protected $thumb;

    public function __construct(SplFileInfo $svg)
    {
        $doc = new DOMDocument();
        $doc->load($svg->getPathname());

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('svg', self::svgNS);
        $this->title = trim($xpath->query('/svg:svg/svg:title')->item(0)->nodeValue);
        $this->desc = json_decode($xpath->query('/svg:svg/svg:desc')->item(0)->nodeValue, true);
        unset($this->desc['form']['seed']);

        $content = $xpath->query('/svg:svg/svg:g[@class="building"]')->item(0);

        $this->thumb = new DOMDocument();
        $root = $this->thumb->createElementNS(self::svgNS, 'svg');
        $root->setAttribute('viewBox', $doc->firstChild->attributes->getNamedItem('viewBox')->nodeValue);
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

    public function __destruct()
    {
        unset($this->thumb);
    }

    public function getRecipe(): string
    {
        return array_search($this->desc['recipe'], MapCrud::model);
    }

    public function getFormData(): array
    {
        return $this->desc['form'];
    }

}
