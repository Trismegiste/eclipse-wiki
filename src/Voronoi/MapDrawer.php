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

    protected SquareGrid $map;

    public function setMap(SquareGrid $map)
    {
        $this->map = $map;
    }

    /**
     * Encloses the map in a disk
     * @param HexaCell $filling The template for outside cell
     */
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

    /**
     * Limits the map to a torus
     * @param HexaCell $filling
     * @param float $innerRadius
     */
    public function drawTorusContainer(HexaCell $filling, float $innerRadius = 0.5): void
    {
        $size = $this->map->getSize();
        $radius = $size / 2.0 - 1;
        $smallRadius = $innerRadius * $size / 2.0;
        $cx = $size / 2.0 - 1;
        $cy = $size / 2.0 - 1;

        for ($x = 0; $x < $size; $x++) {
            for ($y = 0; $y < $size; $y++) {
                $distance = sqrt(($x - $cx) ** 2 + ($y - $cy) ** 2);
                if (($distance >= $radius) || ($distance < $smallRadius)) {
                    $this->map->setCell([$x, $y], clone $filling);
                }
            }
        }
    }

    /**
     * Sets the seeds for the Voronoi cellular automaton
     * @param HexaCell $floorTemplate
     * @param int $avgTilePerRoom The average size of a cluster after growth of the cellular automaton
     */
    public function plantRandomSeed(HexaCell $floorTemplate, int $avgTilePerRoom)
    {
        $size = $this->map->getSize();

        for ($k = 0; $k < $size * $size / $avgTilePerRoom; $k++) {
            $tile = clone $floorTemplate;
            $tile->uid += $k;
            $this->map->setCell([rand(0, $size - 1), rand(0, $size - 1)], $tile);
        }
    }

    public function horizontalCross(HexaCell $hallway, int $howMany = 1, bool $double = false): void
    {
        $size = $this->map->getSize();

        for ($y = $size / ($howMany + 1); $y < $size; $y += $size / ($howMany + 1)) {
            $this->drawHorizontalLine($hallway, (int) $y, 0, $size);
            if ($double) {
                $this->drawHorizontalLine($hallway, (int) $y + 1, 0, $size);
            }
        }
    }

    public function verticalCross(HexaCell $hallway, int $howMany = 1, bool $double = false): void
    {
        $size = $this->map->getSize();

        for ($x = $size / ($howMany + 1); $x < $size; $x += $size / ($howMany + 1)) {
            $this->drawVerticalLine($hallway, (int) $x, 0, $size);
            if ($double) {
                $this->drawVerticalLine($hallway, (int) $x + 1, 0, $size);
            }
        }
    }

    public function drawHorizontalLine(HexaCell $filling, int $y, int $from, int $to): void
    {
        for ($x = $from; $x < $to; $x++) {
            $this->map->setCell([$x, $y], clone $filling);
        }
    }

    public function drawVerticalLine(HexaCell $filling, int $x, int $from, int $to): void
    {
        for ($y = $from; $y < $to; $y++) {
            $this->map->setCell([$x, $y], clone $filling);
        }
    }

    /**
     * Encloses the map with a border
     * @param HexaCell $filling the template cell for the border
     */
    public function drawFrame(HexaCell $filling): void
    {
        $size = $this->map->getSize();

        $this->drawHorizontalLine($filling, 0, 0, $size);
        $this->drawHorizontalLine($filling, $size - 1, 0, $size);
        $this->drawVerticalLine($filling, 0, 0, $size);
        $this->drawVerticalLine($filling, $size - 1, 0, $size);
    }

    /**
     * Draws a circle large enough to walk into
     * @param HexaCell $filling
     * @param type $radiusRatio
     */
    public function circle(HexaCell $filling, $radiusRatio = 0.75): void
    {
        $size = $this->map->getSize();

        $radius = $radiusRatio * $size / 2.0;
        $cx = $size / 2.0 - 1;
        $cy = $size / 2.0 - 1;

        for ($x = 0; $x < $size; $x++) {
            for ($y = 0; $y < $size; $y++) {
                if (abs(sqrt(($x - $cx) ** 2 + ($y - $cy) ** 2) - $radius) < 1.0) {
                    $this->map->setCell([$x, $y], clone $filling);
                }
            }
        }
    }

    public function getCanvasSize(): int
    {
        return $this->map->getSize();
    }

    /**
     * Encloses a region with a template cell. The region is any closed polygon
     * @param HexaCell $filling
     * @param array $polygon A list of coordinates of points
     */
    public function fillOutsidePolygon(HexaCell $filling, array $polygon): void
    {
        $size = $this->map->getSize();
        // algo Point In Polygon (PIP) https://en.wikipedia.org/wiki/Point_in_polygon
        for ($x = 0; $x < $size; $x++) {
            for ($y = 0; $y < $size; $y++) {
                // for each point of the canvas
                // https://stackoverflow.com/a/16391873
                $inside = false;
                for ($i = 0, $j = count($polygon) - 1; $i < count($polygon); $j = $i++) {
                    if (( $polygon[$i][1] > $y) != ( $polygon[$j][1] > $y ) &&
                            $x < ( $polygon[$j][0] - $polygon[$i][0] ) * ( $y - $polygon[$i][1] ) / ( $polygon[$j][1] - $polygon[$i][1] ) + $polygon[$i][0]) {
                        $inside = !$inside;
                    }
                }
                if (!$inside) {
                    $this->map->setCell([$x, $y], clone $filling);
                }
            }
        }
    }

}
