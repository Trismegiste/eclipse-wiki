<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Exterior scene
 */
class Exterior extends Vertex
{

    public $lighting;
    public $pressure;
    public $gravity;
    public $parent = null;
    public $youtubeUrl = null;

}
