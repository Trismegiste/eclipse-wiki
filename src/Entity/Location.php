<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Description of a location
 */
class Location extends Vertex
{

    public $lighting;
    public $hearing;
    public $atmosphere;
    public $sight;
    public $smell;
    public $parent = null;
    public $youtubeUrl = null;

}
