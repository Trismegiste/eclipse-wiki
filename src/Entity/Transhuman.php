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

    const avatarSection = '==Avatar==';

    protected $background;
    protected $faction;

    public function __construct(string $title, Background $bg, Faction $fac)
    {
        parent::__construct($title);
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

    public function hasAvatarSection(): bool
    {
        return false !== strpos($this->content, self::avatarSection);
    }

    public function appendAvatarSection(string $filename): void
    {
        $append = "\n" . self::avatarSection . "\n[[file:$filename]]\n";
        $this->content .= $append;
    }

}
