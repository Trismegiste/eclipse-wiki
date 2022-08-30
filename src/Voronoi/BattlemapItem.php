<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

/**
 * A tile, a token, a part from a battlemap with multiple layer
 */
interface BattlemapItem
{

    public function dumpGround(float $cx, float $y): void;

    public function dumpWall(float $cx, float $y): void;

    public function dumpDoor(float $cx, float $y): void;

    public function dumpLegend(string $txt, float $x, float $y): void;
}
