<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

/**
 * Contract for a square grid
 */
interface SquareGrid
{

    /**
     * Gets the size of this square grid
     * @return int
     */
    public function getSize(): int;

    /**
     * Sets the content of this cell
     * @param array $coord
     * @param HexaCell $cell
     */
    public function setCell(array $coord, HexaCell $cell): void;

    /**
     * Gets the content at given coordinates
     * @param array $coord
     * @return HexaCell
     */
    public function getCell(array $coord): HexaCell;
}
