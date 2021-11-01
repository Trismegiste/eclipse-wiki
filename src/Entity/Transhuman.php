<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * A Transhuman character
 */
class Transhuman extends Character
{

    protected $background;
    protected $faction;

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

    public function getDescription(): string
    {
        return $this->background->title . ' - ' . $this->faction->title;
    }

}
