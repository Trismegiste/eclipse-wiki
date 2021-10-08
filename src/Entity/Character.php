<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * A Character
 */
class Character
{

    protected $background;
    protected $faction;
    protected $morph;

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

}
