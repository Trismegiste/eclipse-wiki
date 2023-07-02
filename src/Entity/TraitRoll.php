<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * A roll in SaWo
 */
class TraitRoll
{

    protected string $label;
    protected int $side;
    protected int $modifier = 0;
    protected bool $altered = false;

    public function __construct(SaWoTrait $trait, ?TraitBonus $bonus = null)
    {
        $this->altered = !is_null($bonus) && ($bonus->dieType !== 0);

        $this->label = $trait->getName();
        $this->modifier = $trait->modifier + $bonus?->flat;

        $side = $trait->dice + 2 * $bonus?->dieType;
        if ($side > 12) {
            $this->modifier += ($side - 12) / 2;
            $side = 12;
        }
        $this->side = $side;
    }

    public function getDice(): int
    {
        return $this->side;
    }

    public function getModifier(): int
    {
        return $this->modifier;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isAltered(): bool
    {
        return $this->altered;
    }

}
