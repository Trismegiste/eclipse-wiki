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
    public $name; // the name of this character
    public $attributes = [];
    protected $background;
    protected $faction;
    protected $morph;
    protected $skills = [];
    protected $gears = [];
    protected $attacks = [];
    public $armor;
    public $morphArmor = 0;
    public $rangedMalus = 0;

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

    public function getAttributePoints(): int
    {
        $cpt = 0;
        foreach ($this->attributes as $item) {
            /** @var \App\Entity\Attribute $item */
            $cpt += $item->dice / 2 - 2 + $item->modifier;
        }

        return $cpt;
    }

    public function getPowerIndex(): int
    {
        return ($this->getAttributePoints() - 5) +
            ($this->getSkillPoints() - 12) / 2 +
            (count($this->edges) - 1);
    }

    public function getGears(): array
    {
        return $this->gears;
    }

    public function setGears(array $listing)
    {
        $this->gears = $listing;
    }

    public function getAttacks(): array
    {
        return $this->attacks;
    }

    public function setAttacks(array $listing)
    {
        $this->attacks = $listing;
    }

    public function getParry(): int
    {
        $parry = 2;
        $fighting = $this->searchSkillByName('Combat');
        if (!is_null($fighting)) {
            /** @var Skill $fighting */
            $parry = 2 + $fighting->dice / 2 + (int) floor($fighting->modifier / 2);
        }

        return $parry;
    }

    public function searchSkillByName(string $name): ?Skill
    {
        foreach ($this->skills as $skill) {
            /** @var Skill $skill */
            if ($name === $skill->getName()) {
                return $skill;
            }
        }

        return null;
    }

    public function getAttributeByName(string $name): ?Attribute
    {
        foreach ($this->attributes as $attr) {
            /** @var Attribute $attr */
            if ($name === $attr->getName()) {
                return $attr;
            }
        }

        throw new \InvalidArgumentException("$name is not an valid attribute");
    }

    /**
     * As per rules, Toughness should includes armor values
     * @return int
     */
    public function getToughness(): int
    {
        $vigor = $this->getAttributeByName('Vigueur');

        $toughness = 2 + $vigor->dice / 2 + (int) floor($vigor->modifier / 2);
        $toughness += $this->getTotalArmor();

        return $toughness;
    }

    public function getTotalArmor(): int
    {
        // if there are two armors :
        if ($this->armor) {
            // take the max and add half of the second
            $cumul = [$this->armor->protect, $this->morphArmor];
            sort($cumul);

            return $cumul[1] + (int) floor($cumul[0] / 2);
        } else {
            return $this->morphArmor;
        }
    }

}
