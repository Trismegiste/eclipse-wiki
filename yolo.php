<?php

require_once __DIR__ . '/vendor/autoload.php';

$fac = new App\Entity\Wfc\Factory();

$hex = new App\Entity\HexagonalTile();
$hex->filename = 'sea';
$hex->setAnchor(['b', 'b', 'b', 'b', 'b', 'b']);

$arrang = new App\Entity\TileArrangement();
$arrang->setCollection([$hex]);
$wf = $fac->buildWaveFunction(2, $arrang);

var_dump($wf);
