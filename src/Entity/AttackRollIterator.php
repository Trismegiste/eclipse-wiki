<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * Iterator on attacks
 */
class AttackRollIterator extends \ArrayIterator
{

    public function __construct(array $attackList, protected Morph $morph)
    {
        parent::__construct($attackList);
    }

    public function current(): mixed
    {
        /** @var Attack $attack */
        $attack = parent::current();
        $currentTrait = $attack->roll;
        $bonus = $this->morph->searchSkillBonus($currentTrait->getName());

        return new AttackRoll($attack, $bonus);
    }

    public function offsetGet(mixed $key): AttackRoll
    {
        $attack = parent::offsetGet($key);
        $currentTrait = $attack->roll;
        $bonus = $this->morph->searchSkillBonus($currentTrait->getName());

        return new AttackRoll($attack, $bonus);
    }

}
