<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Wfc;

use Trismegiste\MapGenerator\SvgPrintable;
use Trismegiste\Strangelove\Type\BsonFixedArray;

/**
 * A Map tiled with hexagons
 */
class WaveFunction implements SvgPrintable
{

    protected $tile;

    public function __construct(int $size)
    {
        $this->tile = new BsonFixedArray($size);
        for ($k = 0; $k < $size; $k++) {
            $this->tile[$k] = new BsonFixedArray($size);
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

    /**
     * Sets a tile
     * @param array $coord
     * @param WaveCell $tile
     * @return void
     */
    public function setTile(array $coord, WaveCell $tile): void
    {
        $this->tile[$coord[0]][$coord[1]] = $tile;
    }

    /**
     * Gets the coordinates of neighbour tiles around a given tile coordinates
     * @param array $coord
     * @return array
     */
    public function getNeighbourCoordinates(array $coord): array
    {
        $x = $coord[0];
        $y = $coord[1];
        $offset = $x + ($y % 2);

        return [
            [$x - 1, $y],
            [$x + 1, $y],
            [$offset - 1, $y - 1],
            [$offset, $y - 1],
            [$offset - 1, $y + 1],
            [$offset, $y + 1]
        ];
    }

    /**
     * Gets the tile at a given coordinates
     * @param array $coord
     * @return WaveCell
     */
    public function getTile(array $coord): WaveCell
    {
        return $this->tile[$coord[0]][$coord[1]];
    }

}
