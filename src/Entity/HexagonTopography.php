<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use SplFixedArray;

/**
 * A Map tiled with hexagons
 */
class HexagonTopography implements \Trismegiste\MapGenerator\SvgPrintable
{

    protected $tile;

    public function __construct(int $size)
    {
        $this->tile = new SplFixedArray($size);
        for ($k = 0; $k < $size; $k++) {
            $this->tile[$k] = new SplFixedArray($size);
        }
    }

    public function printSvg(): void
    {
        $sin60 = sin(M_PI / 3);
        $tan60 = tan(M_PI / 3);

        foreach ($this->tile as $x => $column) {
            foreach ($column as $y => $cell) {
                $cx = ($x - floor($y / 2)) / $sin60 + $y / $tan60;
                echo "<use x=\"$cx\" y=\"$y\" href=\"#$cell\">";
                echo "<title>$x $y</title>";
                echo "</use>\n";
            }
        }
    }

    public function set(array $coord, $value): void
    {
        $this->tile[$coord[0]][$coord[1]] = $value;
    }

    public function getNeighbour(array $coord): array
    {
        $x = $coord[0];
        $y = $coord[1];
        $offset = $x - 1 + ($y % 2);

        return [
            [$x - 1, $y],
            [$x + 1, $y],
            [$offset, $y - 1],
            [$offset + 1, $y - 1],
            [$offset, $y + 1],
            [$offset + 1, $y + 1]
        ];
    }

}
