<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * A collection of skill rolls in SaWo
 */
class SkillRollIterator extends \ArrayIterator
{

    public function __construct(array $traitList, protected Morph $morph)
    {
        parent::__construct($traitList);
    }

    public function current(): mixed
    {
        $currentTrait = parent::current();
        $bonus = $this->morph->searchSkillBonus($currentTrait->getName());

        return new TraitRoll($currentTrait, $bonus);
    }

}
