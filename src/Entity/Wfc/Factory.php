<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Wfc;

/**
 * Factory for WFC objects
 */
class Factory
{

    protected $tileFolder;

    public function __construct()
    {
        $this->tileFolder = __DIR__ . '/../../../templates/hex/tile/';
    }

    /**
     * Creates a base of EigenTile
     * @param \App\Entity\TileArrangement $arrang
     * @return array
     */
    public function buildEigenTileBase(\App\Entity\TileArrangement $arrang): array
    {
        // Generates all EigenTile from the HexagonalTile collection
        $tileBase = [];
        $anchor = [];
        foreach ($arrang->getCollection() as $tile) {
            $svgFile = new TileSvg();
            $svgFile->load($this->tileFolder . $tile->filename);
            foreach ($tile->getRotation() as $idx => $isPresent) {
                // creating a tile for each possible rotation
                if ($isPresent) {
                    $tileBase[] = new EigenTile($svgFile->getKey(), 60 * $idx);
                    // we copy the tile anchors array and shift it (and loop) according the count of 60° rotations we apply to the tile
                    $tmp = $tile->getAnchor();
                    for ($k = 0; $k < $idx; $k++) {
                        $lastItem = array_pop($tmp);
                        array_unshift($tmp, $lastItem);
                    }
                    $anchor[] = $tmp;
                }
            }
        }

        // compute the 6 neighbours lists with the anchor array of arrays
        foreach ($tileBase as $centerIdx => $centerTile) {
            for ($direction = 0; $direction < 6; $direction++) {
                foreach ($tileBase as $neighborIdx => $neighborTile) {
                    if ($anchor[$centerIdx][$direction] === $anchor[$neighborIdx][($direction + 3) % 6]) {
                        // for example : if the anchor name at EAST of the center tile is equal to the anchor name at WEST of the neighbor tile
                        // we update the list i.e. this neighbour tile could be at EAST of the center tile
                        $centerTile->neighbourList[$direction][] = $neighborTile;
                    }
                }
            }
        }

        return $tileBase;
    }

    public function buildWaveFunction(int $size, array $base): WaveFunction
    {
        $wf = new WaveFunction($size);
        $wf->setEigenBase($base);

        for ($x = 0; $x < $size; $x++) {
            for ($y = 0; $y < $size; $y++) {
                $cell = new \App\Entity\Wfc\WaveCell($base);
                $wf->setCell([$x, $y], $cell);
            }
        }

        return $wf;
    }

    public function buildBattlemap(int $size, \App\Entity\TileArrangement $arrang, array $base): \DOMDocument
    {

        $battlemap = new BattlemapSvg();
        $root = $battlemap->createElementNS(TileSvg::svgNS, 'svg');
        $root->setAttribute('viewBox', "0 0 $size $size");
        $battlemap->appendChild($root);

        $defs = $battlemap->createElementNS(TileSvg::svgNS, 'defs');
        $root->appendChild($defs);

        // hexagon tile from file
        foreach ($arrang->getCollection() as $tile) {
            $svg = new TileSvg();
            $svg->load($this->tileFolder . $tile->filename);
            $item = $svg->getTile();
            $imported = $battlemap->importNode($item, true);
            $defs->appendChild($imported);
        }

        // generation of eigentile
        foreach ($base as $eigentile) {
            /** @var \App\Entity\Wfc\EigenTile $eigentile */
            $item = $battlemap->createElementNS(TileSvg::svgNS, 'g');
            $item->setAttribute('id', $eigentile->getUniqueId());
            $item->setAttribute('transform', "rotate(-" . $eigentile->getRotation() . ")");
            $usetile = $battlemap->createElementNS(TileSvg::svgNS, 'use');
            $usetile->setAttribute('href', '#' . $eigentile->getTemplate());
            $item->appendChild($usetile);
            $defs->appendChild($item);
        }

        // map
        $item = $battlemap->createElementNS(TileSvg::svgNS, 'g');
        $item->setAttribute('id', 'ground');
        $root->appendChild($item);

        return $battlemap;
    }

}
