<?php

const svgNS = 'http://www.w3.org/2000/svg';

$svg = new SplFileInfo(__DIR__ . '/templates/hex/tile/sea.svg');

$doc = new TileSvg();
$doc->load($svg->getPathname());
$content = $doc->getTile();

$battlemap = new DOMDocument();
$root = $battlemap->createElementNS(svgNS, 'svg');
$root->setAttribute('viewBox', "0 0 10 10");
$defs = $battlemap->createElementNS(svgNS, 'defs');
$root->appendChild($defs);
$tile = $battlemap->importNode($content, true);
$defs->appendChild($tile);
$battlemap->appendChild($root);

$ground = $battlemap->createElementNS(svgNS, 'g');
$root->appendChild($ground);

echo $battlemap->saveXML();

class TileSvg extends DOMDocument
{

    protected $key;

    public function load($filename, $options = 0)
    {
        $this->key = pathinfo($filename, PATHINFO_FILENAME);

        return parent::load($filename, $options);
    }

    public function getTile(): \DOMElement
    {
        $xpath = new \DOMXPath($this);
        $xpath->registerNamespace('svg', svgNS);

        return $xpath->query('/svg:svg/svg:g[@id="' . $this->key . '"]')->item(0);
    }

}
