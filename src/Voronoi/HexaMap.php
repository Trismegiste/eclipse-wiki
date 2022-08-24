<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use App\Voronoi\HexaCell;
use App\Voronoi\BattlemapSvg;
use App\Entity\Wfc\TileSvg;

/**
 * A Map tiled with hexagons
 */
class HexaMap
{

    protected int $gridSize;
    protected array $grid;

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
                $hue = ($cell->uid % 20) * 18;
                $sat = ($cell->uid % 2) ? '100%' : '70%';
                $item->setAttribute('fill', "hsl($hue,$sat,50%)");

                $title = $doc->createElementNS(TileSvg::svgNS, 'title');
                $title->textContent = 'room-' . $cell->uid;
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
     * Gets the neighbour cells around a given cell coordinates
     * @param int $x
     * @param int $y
     * @return array
     */
    public function getNeighbourCell(int $x, int $y): array
    {
        $offset = $x + ($y % 2);

        $neighbour = [];

        if ($x > 0) {
            $neighbour[HexaCell::WEST] = $this->grid[$x - 1][$y];
        }

        if ($x < $this->gridSize - 1) {
            $neighbour[HexaCell::EAST] = $this->grid[$x + 1][$y];
        }

        if (($offset > 0) && ($y > 0)) {
            $neighbour[HexaCell::NORTHWEST] = $this->grid[$offset - 1][$y - 1];
        }

        if (($offset < $this->gridSize) && ($y > 0)) {
            $neighbour[HexaCell::NORTHEAST] = $this->grid[$offset][$y - 1];
        }

        if (($offset > 0) && ($y < $this->gridSize - 1)) {
            $neighbour[HexaCell::SOUTHWEST] = $this->grid[$offset - 1][$y + 1];
        }

        if (($offset < $this->gridSize) && ($y < $this->gridSize - 1)) {
            $neighbour[HexaCell::SOUTHEAST] = $this->grid[$offset][$y + 1];
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

    public function iterateNeighbourhood(): bool
    {
        $update = array_fill(0, $this->gridSize, array_fill(0, $this->gridSize, null));

        $hasNull = false;
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $center) {
                /** @var HexaCell $center */
                if (is_null($center)) {
                    $hasNull = true;
                    $neighbor = $this->getNeighbourCell($x, $y);
                    $choices = [];
                    foreach ($neighbor as $direction => $cell) {
                        /** @var HexaCell $cell */
                        if (!is_null($cell) && $cell->growable) {
                            $choices[] = $cell;
                        }
                    }

                    $nbChoices = count($choices);
                    switch ($nbChoices) {
                        case 0:
                            break;

                        case 1:
                            $update[$x][$y] = clone $choices[0];
                            break;

                        default:
                            $picked = $choices[rand(0, $nbChoices - 1)];
                            $update[$x][$y] = clone $picked;
                            foreach ($neighbor as $direction => $cell) {
                                // if two => random choice and add door if access[$room1][$room2] is false
                                // for each direction : add wall
                                if (!is_null($cell) && ($cell->uid !== $picked->uid)) {
                                    $cell->wall[$direction] = true;
                                }
                            }
                    }
                } else {
                    $update[$x][$y] = clone $center;
                }
            }
        }

        unset($this->grid);
        $this->grid = $update;

        return $hasNull;
    }

}
