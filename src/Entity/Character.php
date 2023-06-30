<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use Trismegiste\Strangelove\MongoDb\RootImpl;

/**
 * A Character
 */
abstract class Character extends Vertex implements \JsonSerializable
{

    const TOUGHNESS_ATTR = 'Vigueur';

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
    public array $economy = [];
    public ?string $tokenPic = null;

    public function setMorph(Morph $mrp): void
    {
        $this->morph = $mrp;
    }

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

    public function jsonSerialize(): mixed
    {
        return $this->bsonSerialize();
    }

    public function getSkillPoints(): int
    {
        $cpt = 0;
        foreach ($this->skills as $item) {
            /** @var Skill $item */
            $cpt += $item->dice / 2 - 1 + $item->modifier;
        }

        return $cpt;
    }

    public function getAttributePoints(): int
    {
        $cpt = 0;
        foreach ($this->attributes as $item) {
            /** @var Attribute $item */
            $cpt += $item->dice / 2 - 2 + $item->modifier;
        }

        return $cpt;
    }

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

    public function getParry(): int
    {
        $parry = 2;
        $fighting = $this->searchSkillByName('Combat');
        if (!is_null($fighting)) {
            /** @var Skill $fighting */
            $parry = 2 + $fighting->dice / 2 + (int) floor($fighting->modifier / 2);
        }

        return $parry + $this->parryBonus;
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
        $vigor = $this->getAttributeByName(self::TOUGHNESS_ATTR);

        $toughness = 2 + $vigor->dice / 2 + (int) floor($vigor->modifier / 2);
        $toughness += $this->toughnessBonus;
        $toughness += $this->getTotalArmor();

        return $toughness;
    }

    public function getTotalArmor(): int
    {
        // only torso armors, as per SaWo rules
        $torso = array_filter($this->armors, function (Armor $a) {
            return false !== strpos($a->zone, 'T');
        });

        // reverse order
        usort($torso, function (Armor $a, Armor $b) {
            return $b->protect - $a->protect;
        });

        switch (count($torso)) {
            case 0 : return 0;
            case 1 : return $torso[0]->protect;
            default : return $torso[0]->protect + (int) floor($torso[1]->protect / 2);
        }
    }

    abstract public function getDescription(): string;

    public function getSkillRolls(): \Iterator
    {
        return new SkillRollIterator($this->skills, $this->morph->skillBonus);
    }

    public function getAttributeRolls(): \Iterator
    {
        return new AttributeRollIterator($this->attributes, $this->morph->attributeBonus);
    }

    public function getAttributeRollByName(string $name): TraitRoll
    {
        $attr = $this->getAttributeByName($name);

        return new TraitRoll($attr);
    }

}
