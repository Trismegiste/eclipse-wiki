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

    public function plantRandomSeed(HexaCell $floorTemplate, int $avgTilePerRoom)
    {
        $size = $this->map->getSize();

        for ($k = 0; $k < $size * $size / $avgTilePerRoom; $k++) {
            $tile = clone $floorTemplate;
            $tile->uid += $k;
            $this->map->setCell([rand(0, $size - 1), rand(0, $size - 1)], $tile);
        }
    }

    public function drawHorizontalLine(HexaCell $hallway, int $howMany = 1, int $thickness = 1): void
    {
        $size = $this->map->getSize();

        for ($y = $size / ($howMany + 1); $y < $size; $y += $size / ($howMany + 1)) {
            for ($x = 0; $x < $size; $x++) {
                $this->map->setCell([$x, (int) floor($y)], clone $hallway);
            }
        }
    }

}
