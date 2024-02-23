<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use Trismegiste\Strangelove\MongoDb\RootImpl;

/**
 * A Character
 */
abstract class Character extends Vertex implements \JsonSerializable, Fighter
{

    const TOUGHNESS_ATTR = 'Vigueur';
    const PARRY_SKILL = 'Combat';
    const SECURITY_SKILL = 'Recherche';

    use RootImpl;
    use EdgeContainer;

    public bool $wildCard = false;
    public array $attributes = [];
    protected ?Morph $morph = null;
    protected array $skills = [];
    protected array $gears = [];
    protected array $attacks = [];
    protected array $armors = [];
    public int $rangedMalus = 0;
    public int $toughnessBonus = 0;
    public int $parryBonus = 0;
    public int $securityBonus = 0;
    public array $economy = [];
    public ?string $tokenPic = null;

    /**
     * Mutator of the morph
     * @param Morph $mrp
     * @return void
     */
    public function setMorph(Morph $mrp): void
    {
        $this->morph = $mrp;
    }

    /**
     * Accessor of the morph
     * @return Morph|null
     */
    public function getMorph(): ?Morph
    {
        return $this->morph;
    }

    protected function beforeSave(): void
    {
        parent::beforeSave();

        usort($this->skills, function (Skill $a, Skill $b) {
            return strcmp(iconv('UTF-8', 'ASCII//TRANSLIT', $a->getName()), iconv('UTF-8', 'ASCII//TRANSLIT', $b->getName()));
        });
        $this->skills = array_values($this->skills);
    }

    /**
     * Adds a non-existing Skill to this Character
     * @param Skill $sk
     * @return void
     */
    public function addSkill(Skill $sk): void
    {
        foreach ($this->skills as $item) {
            if ($item->getName() === $sk->getName()) {
                return;
            }
        }

        $this->skills[] = $sk;
    }

    /**
     * Removes a Skill from this Character
     * @param Skill $sk
     * @return void
     */
    public function removeSkill(Skill $sk): void
    {
        foreach ($this->skills as $idx => $item) {
            if ($item->getName() === $sk->getName()) {
                unset($this->skills[$idx]);
            }
        }
    }

    /**
     * Gets the list of all Skills
     * @return array
     */
    public function getSkills(): array
    {
        return $this->skills;
    }

    public function jsonSerialize(): mixed
    {
        return $this->bsonSerialize();
    }

    /**
     * Returns a rough evaluation of how many progressions are spent in all Skills
     * @return int
     */
    public function getSkillPoints(): int
    {
        $cpt = 0;
        foreach ($this->skills as $item) {
            /** @var Skill $item */
            $cpt += $item->dice / 2 - 1 + $item->modifier;
        }

        return $cpt;
    }

    /**
     * Returns a rough evaluation of how many progressions are spent in all Attributes
     * @return int
     */
    public function getAttributePoints(): int
    {
        $cpt = 0;
        foreach ($this->attributes as $item) {
            /** @var Attribute $item */
            $cpt += $item->dice / 2 - 2 + $item->modifier;
        }

        return $cpt;
    }

    /**
     * Returns a rough evaluation of how many progressions are spent in this Character
     * @return int
     */
    public function getPowerIndex(): int
    {
        return (int) floor(($this->getAttributePoints() - 5) +
                        ($this->getSkillPoints() - 12) / 2 +
                        (count($this->edges) - 1));
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

    public function getArmors(): array
    {
        return $this->armors;
    }

    public function setArmors(array $listing)
    {
        $this->armors = $listing;
    }

    /**
     * Returns the parry secondary trait for this Character (for defense against melee attacks)
     * @return int
     */
    public function getParry(): int
    {
        $parry = 2;
        $fighting = $this->searchSkillByName(self::PARRY_SKILL);
        if (!is_null($fighting)) {
            /** @var Skill $fighting */
            $parry = 2 + $fighting->dice / 2 + (int) floor($fighting->modifier / 2);
        }

        return $parry + $this->parryBonus;
    }

    /**
     * Returns the security secondary trait for this Character (for defense against hacking)
     * @return int
     */
    public function getSecurity(): int
    {
        $security = 2;
        $skill = $this->searchSkillByName(self::SECURITY_SKILL);
        if (!is_null($skill)) {
            /** @var Skill $skill */
            $security = 2 + $skill->dice / 2 + (int) floor($skill->modifier / 2);
        }

        return $security + $this->securityBonus;
    }

    /**
     * Searches a skill in this character, or null if not found
     * @param string $name
     * @return Skill|null
     */
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

    /**
     * Searches an attribute in this character, or throw exception if not found
     * @param string $name
     * @return Attribute|null
     * @throws \InvalidArgumentException
     */
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
        $vigor = $this->getAttributeByName(self::TOUGHNESS_ATTR);
        $bonus = !is_null($this->morph) ? $this->morph->searchAttributeBonus($vigor->getAbbrev()) : null;
        $roll = new TraitRoll($vigor, $bonus);

        $toughness = 2 + $roll->getDice() / 2 + (int) floor($roll->getModifier() / 2);
        $toughness += $this->toughnessBonus;
        $toughness += $this->morph?->toughnessBonus;
        $toughness += $this->getTotalArmor();

        return $toughness;
    }

    /**
     * Calculates the armor value for the torso
     * @return int
     */
    public function getTotalArmor(): int
    {
        // only torso armors, as per SaWo rules
        $torso = array_filter($this->armors, function (Armor $a) {
            return false !== strpos($a->zone, 'T');
        });
        // adding armor from morph
        if ($this->morph && ($this->morph->bodyArmor > 0)) {
            $tmp = new Armor($this->morph->getUId(), $this->morph->bodyArmor);
            $torso[] = $tmp;
        }

        // reverse sorting armor by the most powerful to the less
        usort($torso, function (Armor $a, Armor $b) {
            return $b->protect - $a->protect;
        });

        switch (count($torso)) {
            // if no armor : bonus = 0
            case 0 : return 0;
            // if one armor, return the value
            case 1 : return $torso[0]->protect;
            // if 2 armor of more, only summing the first and the second and halfing the second, as per rules
            default : return $torso[0]->protect + (int) floor($torso[1]->protect / 2);
        }
    }

    /**
     * A summary of this character, must be implemented
     */
    abstract public function getDescription(): string;

    /**
     * Gets the list of all Skill rolls fully modified by the morph
     * @return \Iterator
     */
    public function getSkillRolls(): \Iterator
    {
        return new SkillRollIterator($this->skills, $this->morph);
    }

    /**
     * Gets the list of all Attribute rolls fully modified by the morph
     * @return \Iterator
     */
    public function getAttributeRolls(): \Iterator
    {
        return new AttributeRollIterator($this->attributes, $this->morph);
    }

    /**
     * Gets the list of all Attack rolls fully modified by the morph
     * @return \Iterator
     */
    public function getAttackRolls(): \Iterator
    {
        return new AttackRollIterator($this->attacks, $this->morph);
    }

    /**
     * Returns the malus against this Character when attacked by a ranged attack
     * @return int
     */
    public function getMalusAgainstRangedAttack(): int
    {
        return $this->rangedMalus;
    }

    /**
     * Is this Character a Wild Card ?
     * @return bool
     */
    public function isWildcard(): bool
    {
        return $this->wildCard;
    }

    /**
     * Returns the profile picture filename for social network profile
     * @return string|null
     */
    public function getTokenPicture(): ?string
    {
        return $this->tokenPic;
    }

}
