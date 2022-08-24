<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use App\Voronoi\HexaCell;
use App\Voronoi\BattlemapSvg;
use App\Voronoi\TileSvg;

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

        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                if (is_null($cell)) {
                    continue;
                }
                /** @var HexaCell $cell */
                $cx = ($x - floor($y / 2)) / $sin60 + $y / $tan60;

                // Ground layer
                $item = $doc->createElementNS(TileSvg::svgNS, 'use');
                $item->setAttribute('x', $cx);
                $item->setAttribute('y', $y);
                $item->setAttribute('href', '#' . $cell->template);
                // color
                $hue = ($cell->uid % 20) * 18;
                $sat = ($cell->uid % 2) ? '100%' : '70%';
                $item->setAttribute('fill', "hsl($hue,$sat,50%)");

                $title = $doc->createElementNS(TileSvg::svgNS, 'title');
                $title->textContent = 'room-' . $cell->uid;
                $item->appendChild($title);

                $doc->getGround()->appendChild($item);

                // Wall layer - Since wall are set on each two cells, no need to duplicate the rendering
                for ($direction = HexaCell::EAST; $direction < HexaCell::WEST; $direction++) {
                    if ($cell->wall[$direction]) {
                        $item = $doc->createElementNS(TileSvg::svgNS, 'use');
                        $item->setAttribute('href', '#eastwall');
                        $angle = -60 * $direction;
                        $item->setAttribute('transform', "translate($cx $y) rotate($angle)");
                        $doc->getWall()->appendChild($item);
                    }
                }

                // Door layer
                for ($direction = HexaCell::EAST; $direction <= HexaCell::SOUTHEAST; $direction++) {
                    if ($cell->door[$direction]) {
                        $item = $doc->createElementNS(TileSvg::svgNS, 'use');
                        $item->setAttribute('href', '#eastdoor');
                        $angle = -60 * $direction;
                        $item->setAttribute('transform', "translate($cx $y) rotate($angle)");
                        $doc->getDoor()->appendChild($item);
                    }
                }
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

    /**
     * Growing Voronoi
     * @return bool if there is still empty tile ?
     */
    public function iterateNeighbourhood(): bool
    {
        $update = array_fill(0, $this->gridSize, array_fill(0, $this->gridSize, null));

        $hasNull = false;
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $center) {
                /** @var HexaCell $center */
                if (is_null($center)) {
                    $hasNull = true;

                    $choices = array_filter($this->getNeighbourCell($x, $y), function (?HexaCell $cell) {
                        return !is_null($cell) && $cell->growable;
                    });

                    $nbChoices = count($choices);
                    switch ($nbChoices) {
                        case 0:
                            break;

                        case 1:
                            $update[$x][$y] = clone array_pop($choices);
                            break;

                        default:
                            $choices = array_values($choices);
                            $picked = $choices[rand(0, $nbChoices - 1)];
                            $update[$x][$y] = clone $picked;
                    }
                } else {
                    $update[$x][$y] = $center;
                }
            }
        }

        $this->grid = $update;

        return $hasNull;
    }

    protected function getCoordPerRoom(): array
    {
        $roomGroup = [];
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                /** @var HexaCell $cell */
                $roomGroup[$cell->uid][] = [$x, $y];
            }
        }

        return $roomGroup;
    }

    public function wallProcessing(): void
    {
        $roomConnection = [];

        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $center) {
                /** @var HexaCell $center */
                $neighbor = $this->getNeighbourCell($x, $y);
                foreach ($neighbor as $direction => $cell) {
                    /** @var HexaCell $cell */
                    if ($center->uid !== $cell->uid) {
                        $center->wall[$direction] = true;
                        if (rand(0, 1))
                            $center->door[$direction] = true;
                    }
                }
            }
        }
    }

}
