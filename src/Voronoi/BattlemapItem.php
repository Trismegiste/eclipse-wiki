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

    /**
     * Dumps the lower layer : the ground
     * @param float $cx
     * @param float $y
     */
    public function dumpGround(float $cx, float $y): void;

    /**
     * Dumps the wall layer of this cell
     * @param float $cx
     * @param float $y
     */
    public function dumpWall(float $cx, float $y): void;

    /**
     * Dumps to stdout the doors layer of this cell
     * @param float $cx
     * @param float $y
     */
    public function dumpDoor(float $cx, float $y): void;

    /**
     * Dumps a given legend in the cell
     * @param string $txt the legend to print
     * @param float $x
     * @param float $y
     */
    public function dumpLegend(string $txt, float $x, float $y): void;

    /**
     * Dumps any NPC on this cell
     * @param float $x
     * @param float $y
     */
    public function dumpNpc(float $x, float $y): void;
}
