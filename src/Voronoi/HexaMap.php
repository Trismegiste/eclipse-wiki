<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use App\Voronoi\HexaCell;

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

    public function getAbscissa(int $x, int $y): float
    {
        return ($x - floor($y / 2)) / sin(M_PI / 3) + $y / tan(M_PI / 3);
    }

    public function dumpGround(): void
    {
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                if (!is_null($cell)) {
                    /** @var HexaCell $cell */
                    $cell->dumpGround($this->getAbscissa($x, $y), $y);
                }
            }
        }
    }

    public function dumpWall(): void
    {
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                if (!is_null($cell)) {
                    /** @var HexaCell $cell */
                    $cell->dumpWall($this->getAbscissa($x, $y), $y);
                }
            }
        }
    }

    public function dumpDoor(): void
    {
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                if (!is_null($cell)) {
                    /** @var HexaCell $cell */
                    $cell->dumpDoor($this->getAbscissa($x, $y), $y);
                }
            }
        }
    }

    public function dumpLegend(): void
    {
        foreach ($this->getCoordPerRoom() as $uid => $roomCoord) {
            list($x, $y, $cell) = array_pop($roomCoord);
            $cell->dumpLegend($this->num2alpha($uid), $this->getAbscissa($x, $y), $y);
        }
    }

    public function dumpFogOfWar(): void
    {
        foreach ($this->getCoordPerRoom() as $uid => $roomCoord) {
            echo "<g id=\"fog-of-war-$uid\" class=\"fog-of-war\">";
            foreach ($roomCoord as $cell) {
                list($x, $y) = $cell;
                $cx = $this->getAbscissa($x, $y);
                echo "<use xlink:href=\"#fogofwar\" x=\"$cx\" y=\"$y\"/>";
            }
            echo '</g>';
        }
    }

    protected function num2alpha(int $n)
    {
        $r = '';
        for ($i = 1; $n >= 0 && $i < 10; $i++) {
            $r = chr(0x41 + (int) floor($n % pow(26, $i) / pow(26, $i - 1))) . $r;
            $n -= pow(26, $i);
        }

        return $r;
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
                $roomGroup[$cell->uid][] = [$x, $y, $cell];
            }
        }

        return $roomGroup;
    }

    /**
     * Add walls and doors on cells
     * @return void
     */
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
                        // wall
                        $center->wall[$direction] = true;
                        // door
                        $keys = [$center->uid, $cell->uid];
                        sort($keys);
                        if (!(array_key_exists($keys[0], $roomConnection) &&
                                array_key_exists($keys[1], $roomConnection[$keys[0]]))) {
                            $center->door[$direction] = true;
                            $roomConnection[$keys[0]][$keys[1]] = true;
                        }
                    }
                }
            }
        }
    }

    /**
     * Erodes rooms by eliminating outside cells that does not have at least a given number of neighbours
     * This erosion is enabled only for a given size of room
     * @param HexaCell $hallway The cell to clone to replace eroded cells
     * @param int $minRoomSize The minimum size of a room (in cell) to apply erosion
     * @param int $maxNeighbour The count of minimal neighbours to keep a cell. 6 means 6 neighbouring cells of the same room are mandatory to keep this cell
     * @return void
     */
    public function erodeWith(HexaCell $hallway, int $minRoomSize = 13, int $maxNeighbour = 6): void
    {
        $update = array_fill(0, $this->gridSize, array_fill(0, $this->gridSize, null));

        $sizePerRoom = array_map(function (array $coord) {
            return count($coord);
        }, $this->getCoordPerRoom());

        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $center) {
                /** @var HexaCell $center */
                $update[$x][$y] = $center;

                if ($center->growable && ($sizePerRoom[$center->uid] > $minRoomSize)) {
                    $neighbor = $this->getNeighbourCell($x, $y);
                    $counter = 0;
                    foreach ($neighbor as $cell) {
                        if ($center->uid === $cell->uid) {
                            $counter++;
                        }
                    }
                    if ($counter < $maxNeighbour) {
                        $update[$x][$y] = clone $hallway;
                    }
                }
            }
        }
        $this->grid = $update;
    }

}
