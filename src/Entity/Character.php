<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use Trismegiste\Toolbox\MongoDb\Root;
use Trismegiste\Toolbox\MongoDb\RootImpl;

/**
 * A Character
 */
class Character implements Root
{

    use RootImpl {
        bsonSerialize as defaultDump;
    }

    protected $background;
    protected $faction;
    protected $morph;
    public $attributes = [];
    public $skills = [];
    protected $wildCard = false;

    public function __construct(Background $bg, Faction $fac)
    {
        $this->background = $bg;
        $this->faction = $fac;
    }

    public function getBackground(): Background
    {
        return $this->background;
    }

    public function getFaction(): Faction
    {
        return $this->faction;
    }

    public function setMorph(Morph $mrp)
    {
        $this->morph = $mrp;
    }

    public function getMorph(): ?Morph
    {
        return $this->morph;
    }

    public function bsonSerialize()
    {
        $this->skills = array_values($this->skills);

        return $this->defaultDump();
    }

}
