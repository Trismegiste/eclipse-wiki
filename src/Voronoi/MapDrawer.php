<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

/**
 * Draw geometric shapes in a HexaMap
 */
class MapDrawer
{

    protected HexaMap $map;

    public function __construct(HexaMap $map)
    {
        $this->map = $map;
    }

    public function drawCircleContainer(HexaCell $filling): void
    {
        $size = $this->map->getSize();
        $radius = $size / 2.0 - 1;
        $cx = $size / 2.0 - 1;
        $cy = $size / 2.0 - 1;

        for ($x = 0; $x < $size; $x++) {
            for ($y = 0; $y < $size; $y++) {
                if (sqrt(($x - $cx) ** 2 + ($y - $cy) ** 2) >= $radius) {
                    $this->map->setCell([$x, $y], clone $filling);
                }
            }
        }
    }

}
