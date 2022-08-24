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

    protected int $gridSize;
    protected array $grid;
    protected array $randomAccess;

    public function __construct(int $size)
    {
        $this->gridSize = $size;
        $this->grid = array_fill(0, $size, array_fill(0, $size, null));

        $this->randomAccess = [];
        for ($x = 0; $x < $this->gridSize; $x++) {
            for ($y = 0; $y < $this->gridSize; $y++) {
                $this->randomAccess[] = [$x, $y];
            }
        }
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
                $hue = ($cell->uid % 20) * 18;
                $sat = ($cell->uid % 2) ? '100%' : '70%';
                $item->setAttribute('fill', "hsl($hue,$sat,50%)");

                $title = $doc->createElementNS(TileSvg::svgNS, 'title');
                $title->textContent = 'cell-' . $cell->uid;
                $item->appendChild($title);

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

    public function iteratePropagation(): bool
    {
        $counter = 0;
        shuffle($this->randomAccess);

        foreach ($this->randomAccess as $idx => $center) {
            /** @var HexaCell $cell */
            $cell = $this->grid[$center[0]][$center[1]];
            if (!is_null($cell)) {
                $neighbor = $this->getNeighbourCoordinates($center);
                foreach ($neighbor as $coord) {
                    if (is_null($this->grid[$coord[0]][$coord[1]])) {
                        $this->setCell($coord, clone $cell);
                        $counter++;
                    }
                }
                unset($this->randomAccess[$idx]); // nothing to propagate further
            }
        }

        return $counter > 0;
    }

    public function iterateNeighborhood(): bool
    {
        $update = array_fill(0, $this->gridSize, array_fill(0, $this->gridSize, null));

        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                if (is_null($cell)) {
                    $neighbor = $this->getNeighbourCoordinates([$x, $y]);
                    foreach ($neighbor as $direction => $coord) {
                        // check neighbour
                        // if zero => skip
                        // if one => clone
                        // if two => random choice and add door if access[$room1][$room2] is false
                        // for each direction : add wall
                    }
                }
            }
        }

        $this->grid = $update;
    }

}
