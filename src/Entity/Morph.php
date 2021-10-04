<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * Morph for a NPC
 */
class Morph
{

    public $title;
    public $ability = [];
    public $disability = [];
    public $type;
    public $price;

    public function __construct(string $param)
    {
        $this->title = $param;
    }

}
