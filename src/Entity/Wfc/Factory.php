<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Wfc;

/**
 * Description of Factory
 *
 * @author trismegiste
 */
class Factory
{

    /**
     * Creates a base of EigenTile
     * @param \App\Entity\TileArrangement $arrang
     * @return array
     */
    public function buildEigenTileBase(\App\Entity\TileArrangement $arrang): array
    {
        // Generates all EigenTile from the HexagonalTile collection
        $tileDic = [];
        $anchor = [];
        foreach ($arrang->getCollection() as $tile) {
            foreach ($tile->getRotation() as $idx => $isPresent) {
                // creating a tile for each possible rotation
                if ($isPresent) {
                    $eigen = new EigenTile();
                    $eigen->filename = $tile->filename;
                    $eigen->rotation = 60 * $idx;

                    $tileDic[] = $eigen;
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

        // compute neighbors masks with the anchor array of arrays
        foreach ($tileDic as $centerIdx => $centerTile) {
            for ($direction = 0; $direction < 6; $direction++) {
                $mask = 0;
                foreach ($tileDic as $neighborIdx => $neighborTile) {
                    if ($anchor[$centerIdx][$direction] === $anchor[$neighborIdx][($direction + 3) % 6]) {
                        // for example : if the anchor name at EAST of the center tile is equal to the anchor name at WEST of the neighbor tile
                        // we update the mask i.e. this neighbor tile could be at EAST of the center tile
                        $mask = $mask | (1 << $neighborIdx);
                    }
                }
                $centerTile->neighborMask[$direction] = $mask;
            }
        }

        return $tileDic;
    }

    public function buildWaveFunction(int $size, \App\Entity\TileArrangement $arrang): WaveFunction
    {
        $base = $this->buildEigenTileBase($arrang);
        $wf = new WaveFunction($size);
        $wf->setEigenBase($base);

        for ($x = 0; $x < $size; $x++) {
            for ($y = 0; $y < $size; $y++) {
                $cell = new \App\Entity\Wfc\WaveCell();
                $cell->tileMask = (1 << count($base)) - 1;  // all EigenTile are possible therefore 0b11111111111
                $wf->setCell([$x, $y], $cell);
            }
        }

        return $wf;
    }

}
