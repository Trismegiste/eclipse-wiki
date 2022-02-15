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
    public $surnameLang = null;
    public $npcTemplate = null;
    public $battleMap = null;

}
