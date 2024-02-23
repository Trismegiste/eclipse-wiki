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

    protected Background $background;
    protected Faction $faction;
    public ?string $surnameLang = null;
    public ?string $tokenPicPrompt = null;
    public ?string $hashtag = null;
    public ?string $instantiatedFrom = null;

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
        if (is_null($this->instantiatedFrom)) {
            return $this->background->title . ' - ' . $this->faction->title;
        } else {
            return 'Instance de ' . $this->instantiatedFrom;
        }
    }

    /**
     * Gets a list of hashtags for this transhuman inherited from his/her faction and background
     * @return string
     */
    public function getDefaultHashtag(): string
    {
        $motiv = array_merge($this->background->motivation, $this->faction->motivation);
        $result = [];
        foreach ($motiv as $suggest) {
            if (preg_match('#^(Pour|Contre)[^:]*:(.+)$#', $suggest, $extract)) {  // robust regex because of some weird whitespace characters
                $prefix = ($extract[1] === 'Contre') ? '#anti-' : '#';
                foreach (explode(',', $extract[2]) as $doct) {
                    $result[] = $prefix . mb_strtolower(str_replace(' ', '-', trim($doct)));
                }
            }
        }

        return implode(' ', array_unique($result));
    }

    public function isNpcTemplate(): bool
    {
        return !empty($this->surnameLang);
    }

}
