<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Faction for a NPC
 */
class Faction implements Indexable
{

    public $title;
    public $characteristic = [];
    public $motivation = [];

    public function __construct(string $param)
    {
        $this->title = $param;
    }

    public function getUId(): string
    {
        return $this->title;
    }

}
