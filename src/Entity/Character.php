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
class Character implements Root, \JsonSerializable
{

    use RootImpl {
        bsonSerialize as defaultDump;
    }
    use EdgeContainer;

    public $wildCard = false;
    protected $background;
    protected $faction;
    protected $morph;
    public $attributes = [];
    protected $skills = [];
    public $name; // the name of this character

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
        usort($this->skills, function (Skill $a, Skill $b) {
            return iconv('UTF-8', 'ASCII//TRANSLIT', $a->getName()) > iconv('UTF-8', 'ASCII//TRANSLIT', $b->getName());
        });
        $this->skills = array_values($this->skills);

        return $this->defaultDump();
    }

    public function addSkill(Skill $sk): void
    {
        foreach ($this->skills as $item) {
            if ($item->getName() === $sk->getName()) {
                return;
            }
        }

        $this->skills[] = $sk;
    }

    public function removeSkill(Skill $sk): void
    {
        foreach ($this->skills as $idx => $item) {
            if ($item->getName() === $sk->getName()) {
                unset($this->skills[$idx]);
            }
        }
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    public function jsonSerialize()
    {
        return $this->bsonSerialize();
    }

    public function getSkillPoints(): int
    {
        $cpt = 0;
        foreach ($this->skills as $item) {
            /** @var \App\Entity\Skill $item */
            $cpt += $item->dice / 2 - 1 + $item->modifier;
        }

        return $cpt;
    }

    public function __clone()
    {
        $this->_id = null;
    }

}
