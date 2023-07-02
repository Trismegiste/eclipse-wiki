<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * An attack roll fully modified
 */
class AttackRoll extends TraitRoll
{

    public function __construct(protected Attack $attack, ?TraitBonus $bonus = null)
    {
        parent::__construct($attack->roll, $bonus);
    }

    public function getTitle(): string
    {
        return $this->attack->title;
    }

    public function getRoll(): TraitRoll
    {
        return $this;
    }

    public function getRollBonus(): int
    {
        return $this->attack->rollBonus;
    }

    public function getRateOfFire(): int
    {
        return $this->attack->rateOfFire;
    }

    public function getDamage(): DamageRoll
    {
        return $this->attack->damage;
    }

    public function getArmorPiercing(): int
    {
        return $this->attack->armorPiercing;
    }

    public function getReach(): string
    {
        return $this->attack->reach;
    }

}
