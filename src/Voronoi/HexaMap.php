<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use App\Entity\HexagonalTile;
use App\Entity\Wfc\BattlemapSvg;
use App\Entity\Wfc\TileSvg;

/**
 * A Map tiled with hexagons
 */
class HexaMap
{

    protected $gridSize;
    protected $grid;

    public function __construct(int $size)
    {
        $this->gridSize = $size;
        $this->grid = array_fill(0, $size, array_fill(0, $size, null));
    }

    public function getSize(): int
    {
        return $this->gridSize;
    }

    public function dump(BattlemapSvg $doc): void
    {
        $sin60 = sin(M_PI / 3);
        $tan60 = tan(M_PI / 3);

        $container = $doc->getGround();
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                if (is_null($cell)) {
                    continue;
                }
                /** @var HexaCell $cell */
                $cx = ($x - floor($y / 2)) / $sin60 + $y / $tan60;
                $item = $doc->createElementNS(TileSvg::svgNS, 'use');
                $item->setAttribute('x', $cx);
                $item->setAttribute('y', $y);
                $item->setAttribute('href', '#' . $cell->template);
                $container->appendChild($item);
            }
        }
    }

    /**
     * Sets a cell of the grid
     * @param array $coord
     * @param HexaCell $cell
     */
    public function setCell(array $coord, HexaCell $cell): void
    {
        $this->grid[$coord[0]][$coord[1]] = $cell;
    }

    /**
     * Gets the coordinates of neighbour cells around a given cell coordinates
     * @param array $coord
     * @return array
     */
    public function getNeighbourCoordinates(array $coord): array
    {
        $x = $coord[0];
        $y = $coord[1];
        $offset = $x + ($y % 2);

        $neighbour = [];

        if ($x > 0) {
            $neighbour[HexagonalTile::WEST] = [$x - 1, $y];
        }

        if ($x < $this->gridSize - 1) {
            $neighbour[HexagonalTile::EAST] = [$x + 1, $y];
        }

        if (($offset > 0) && ($y > 0)) {
            $neighbour[HexagonalTile::NORTHWEST] = [$offset - 1, $y - 1];
        }

        if (($offset < $this->gridSize) && ($y > 0)) {
            $neighbour[HexagonalTile::NORTHEAST] = [$offset, $y - 1];
        }

        if (($offset > 0) && ($y < $this->gridSize - 1)) {
            $neighbour[HexagonalTile::SOUTHWEST] = [$offset - 1, $y + 1];
        }

        if (($offset < $this->gridSize) && ($y < $this->gridSize - 1)) {
            $neighbour[HexagonalTile::SOUTHEAST] = [$offset, $y + 1];
        }

        return $neighbour;
    }

    /**
     * Gets the cell at a given coordinates
     * @param array $coord
     * @return HexaCell
     */
    public function getCell(array $coord): HexaCell
    {
        return $this->grid[$coord[0]][$coord[1]];
    }

    public function iterate(): void
    {
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                /** @var HexaCell $cell */
                if (!is_null($cell)) {
                    $neighbor = $this->getNeighbourCoordinates([$x, $y]);
                    foreach ($neighbor as $coord) {
                        if (is_null($this->grid[$coord[0]][$coord[1]])) {
                            $this->setCell($coord, clone $cell);
                        }
                    }
                }
            }
        }
    }

}
