<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Faction for a NPC
 */
class Faction
{

    public $title;
    public $characteristic = [];
    public $motivation = [];

    public function __construct(string $param)
    {
        $this->title = $param;
    }

}
