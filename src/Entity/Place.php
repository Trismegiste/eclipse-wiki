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
    public $youtubeUrl = null;
    public $battleMap = null;
    public ?MapConfig $voronoiParam = null;

}
