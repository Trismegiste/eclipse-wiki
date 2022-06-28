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

    public $temperature;
    public $pressure;
    public $gravity;
    public $youtubeUrl = null;
    public $npcTemplate = null; // need something more complex, a list and Place children will reuse this list
    public $battleMap = null;

}
