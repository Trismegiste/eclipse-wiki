<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Stage scene
 */
class Place extends Vertex
{

    public $world;
    public $temperature;
    public $pressure;
    public $gravity;
    public ?string $youtubeUrl = null;
    public ?string $battleMap = null;
    public ?string $battlemap3d = null;
    public ?MapConfig $voronoiParam = null;

}
