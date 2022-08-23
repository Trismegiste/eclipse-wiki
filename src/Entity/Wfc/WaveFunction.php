<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Wfc;

use App\Entity\HexagonalTile;

/**
 * A Map tiled with hexagons
 */
class WaveFunction
{

    protected $gridSize;
    protected $grid;
    protected $base;
    protected $lastCollapse; // last coordinates (array) of the last collapsed cell

    public function __construct(int $size)
    {
        $this->gridSize = $size;
        $this->grid = array_fill(0, $size, array_fill(0, $size, null));
        // used for sorting cell with the same entropy
        $this->lastCollapse = [(int) $size / 2, (int) $size / 2];
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
                /** @var \App\Entity\Wfc\WaveCell $cell */
                if ($cell->getEntropy() === 1) {
                    $eigentile = $cell->getFirst();
                    $cx = ($x - floor($y / 2)) / $sin60 + $y / $tan60;
                    $item = $doc->createElementNS(TileSvg::svgNS, 'use');
                    $item->setAttribute('x', $cx);
                    $item->setAttribute('y', $y);
                    $item->setAttribute('href', '#' . $eigentile->getUniqueId());
                    $container->appendChild($item);
                }
            }
        }
    }

    /**
     * Sets a cell of the grid
     * @param array $coord
     * @param WaveCell $cell
     */
    public function setCell(array $coord, WaveCell $cell): void
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
     * @return WaveCell
     */
    public function getCell(array $coord): WaveCell
    {
        return $this->grid[$coord[0]][$coord[1]];
    }

    /**
     * Sets the dictionary of EigenTile
     * @param array $dic Array of EigenTile
     */
    public function setEigenBase(array $dic): void
    {
        // check
        array_walk($dic, function ($val) {
            if (!$val instanceof \App\Entity\Wfc\EigenTile) {
                throw new \UnexpectedValueException("This is not an EigenTile");
            }
        });

        $this->base = $dic;
    }

    /**
     * Finds the coordinates of the cell with minimal entropy (except collapsed) and closest to the last collapsed cell
     * @return array a two-element array for [x,y]
     */
    public function findLowerEntropyCoordinates(): array
    {
        // build an array of cells ordered by entropy (except already collapsed)
        $entropyCounter = [];
        $lowerEntropy = count($this->base);
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                /** @var \App\Entity\Wfc\WaveCell $cell */
                $s = $cell->getEntropy();
                if ($s > 1) {
                    // we store the lower entropy (after 1)
                    if ($s < $lowerEntropy) {
                        $lowerEntropy = $s;
                        // restart the filter of lower entropy
                        $entropyCounter = [[$x, $y]];
                    } else if ($s === $lowerEntropy) {
                        $entropyCounter[] = [$x, $y];
                    }
                }
            }
        }

        $candidate = count($entropyCounter);

        switch ($candidate) {
            case 0 :
                return [];

            case 1 :
                return array_pop($entropyCounter);

            default :
                $closestCoord = [$this->gridSize, $this->gridSize];
                $closestDistance = 2 * $this->gridSize;
                foreach ($entropyCounter as $coord) {
                    $distance = WaveFunction::getManhattanLength($coord, $this->lastCollapse);
                    if ($distance < $closestDistance) {
                        $closestDistance = $distance;
                        $closestCoord = $coord;
                    }
                }
                return $closestCoord;
        }
    }

    static public function getManhattanLength(array $a, array $b): int
    {
        return abs($a[0] - $b[0]) + abs($a[1] - $b[1]);
    }

    public function relaxCoupling(): void
    {
        $dimBase = count($this->base);
        $updated = array_fill(0, $this->gridSize, array_fill(0, $this->gridSize, null));

        for ($x = 0; $x < $this->gridSize; $x++) {
            for ($y = 0; $y < $this->gridSize; $y++) {
                $current = $this->grid[$x][$y];
                if ($current->getEntropy() === 1) {
                    $updated[$x][$y] = clone $current;
                    continue;
                }
                $newCell = $updated[$x][$y] = clone $current;
                $neigh = $this->getNeighbourCoordinates([$x, $y]);
                foreach ($neigh as $direction => $coord) {
                    $couplingWithCurrent = $this->grid[$coord[0]][$coord[1]]->getNeighbourEigenTile(($direction + 3) % 6);
                    if (count($couplingWithCurrent) === $dimBase) {
                        continue;
                    }
                    $newCell->interactWith($couplingWithCurrent);
                }
            }
        }

        $this->grid = $updated;
    }

    public function iterate(): bool
    {
        $coord = $this->findLowerEntropyCoordinates();
        $hasMore = ([] !== $coord);

        if ($hasMore) {
            $this->grid[$coord[0]][$coord[1]]->collapse();
            $this->lastCollapse = $coord;
            $this->relaxCoupling();
        }

        return $hasMore;
    }

    public function propagate(array $center): void
    {
        // echo '   propagate ' . $center[0] . '-' . $center[1] . "\n";
        $current = $this->getCell($center);
        $neighbourCoord = $this->getNeighbourCoordinates($center);

        // filter the cells to be updated
        $interactingCell = [];
        foreach ($neighbourCoord as $direction => $coord) {
            $neighbourCell = $this->getCell($coord);
            if ($neighbourCell->updated) {
                continue;
            }
            $interactingCell[$direction] = $neighbourCell;
        }

        // interact with all (filtered) directions
        foreach ($interactingCell as $direction => $neighbourCell) {
            $constraints = $current->getNeighbourEigenTile($direction);
            $neighbourCell->interactWith($constraints);
            // echo '   interact ' . $neighbourCoord[$direction][0] . '-' . $neighbourCoord[$direction][1] . "\n";
            $neighbourCell->updated = true;
        }

        // and propagate in all (filtered) directions
        foreach ($interactingCell as $direction => $neighbourCell) {
            $this->propagate($neighbourCoord[$direction]);
        }
    }

    public function resetUpdated(): void
    {
        for ($x = 0; $x < $this->gridSize; $x++) {
            for ($y = 0; $y < $this->gridSize; $y++) {
                $this->grid[$x][$y]->updated = false;
            }
        }
    }

    public function newIterate(): bool
    {
        $coord = $this->findLowerEntropyCoordinates();
        $hasMore = ([] !== $coord);

        if ($hasMore) {
            $cell = $this->grid[$coord[0]][$coord[1]];
            $cell->collapse();
            // echo "\nCOLLAPSE " . $coord[0] . '-' . $coord[1] . "\n";
            $this->lastCollapse = $coord;
            $this->propagate($coord);
        }
        $this->resetUpdated();

        return $hasMore;
    }

    public function retryConflict(): void
    {
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                /** @var \App\Entity\Wfc\WaveCell $cell */
                $s = $cell->getEntropy();
                if ($s === 0) {
                    $this->grid[$x][$y] = new WaveCell($this->base);
                }
            }
        }

        while ($this->iterate()) {
            
        }
    }

    // this is MADNESS. Go to Voronoi https://christianjmills.com/Notes-on-Procedural-Map-Generation-Techniques/#voronoi-diagrams
    public function retryHarderConflict(): void
    {
        foreach ($this->grid as $x => $column) {
            foreach ($column as $y => $cell) {
                /** @var \App\Entity\Wfc\WaveCell $cell */
                $s = $cell->getEntropy();
                if ($s === 0) {
                    $this->grid[$x][$y] = new WaveCell($this->base);
                    $resetted = $this->getNeighbourCoordinates([$x, $y]);
                    foreach ($resetted as $coord) {
                        $this->grid[$coord[0]][$coord[1]] = new WaveCell($this->base);
                    }
                }
            }
        }

        while ($this->iterate()) {
            
        }
    }

}
