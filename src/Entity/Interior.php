<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Interior scene
 */
class Interior extends Vertex
{

    public $lighting;
    public $hearing;
    public $atmosphere;
    public $smell;
    public $parent = null;
    public $youtubeUrl = null;

}
