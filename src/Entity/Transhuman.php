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
    protected $title;

    public function __construct(string $title, Background $bg, Faction $fac)
    {
        $this->title = $title;
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
