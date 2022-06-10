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

    public function set(int $x, int $y, $value): void
    {
        $this->tile[$x][$y] = $value;
    }

}
