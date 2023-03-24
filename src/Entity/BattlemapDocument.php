<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * The document for battlemap3d
 */
class BattlemapDocument
{

    public string $theme;
    public int $side;
    public float $wallHeight;
    public array $npcToken = [];
    public array $texture = [];
    public array $grid = [];

}
