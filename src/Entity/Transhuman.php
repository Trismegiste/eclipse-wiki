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

    protected Background $background;
    protected Faction $faction;
    public ?string $surnameLang = null;
    public ?string $hashtag = null;

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
        if (is_null($this->content)) {
            return false;
        }

        return false !== strpos($this->content, self::avatarSection);
    }

    public function appendAvatarSection(string $filename): void
    {
        $append = "\n" . self::avatarSection . "\n[[file:$filename]]\n";
        $this->content .= $append;
    }

    public function getDefaultHashtag(): string
    {
        $motiv = array_merge($this->background->motivation, $this->faction->motivation);
        $result = [];
        foreach ($motiv as $suggest) {
            if (preg_match('#^\s*(\S+)\s*:\s*(.+)$#', $suggest, $extract)) {
                $position = $extract[1];
                $listing = explode(',', $extract[2]);

                $prefix = ($position === 'Contre') ? '#anti-' : '#';
                foreach ($listing as $doct) {
                    $result[] = $prefix . mb_strtolower(str_replace(' ', '-', trim($doct)));
                }
            }
        }

        return implode(' ', $result);
    }

}
