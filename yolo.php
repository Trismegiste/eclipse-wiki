<?php

const svgNS = 'http://www.w3.org/2000/svg';

$svg = new SplFileInfo(__DIR__ . '/templates/hex/tile/sea.svg');

$doc = new DOMDocument();
$doc->load($svg->getPathname());
$key = $svg->getBasename('.svg');

$xpath = new DOMXPath($doc);
$xpath->registerNamespace('svg', svgNS);
$content = $xpath->query('/svg:svg/svg:g[@id="' . $key . '"]')->item(0);

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
