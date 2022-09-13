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

    public function getSize(): int;

    public function setCell(array $coord, HexaCell $cell): void;

    public function getCell(array $coord): HexaCell;
}
