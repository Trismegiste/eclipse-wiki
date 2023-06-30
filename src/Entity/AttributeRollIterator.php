<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * A collection of attribute rolls in SaWo
 */
class AttributeRollIterator extends \ArrayIterator
{

    public function __construct(array $traitList, protected Morph $morph)
    {
        parent::__construct($traitList);
    }

    public function current(): mixed
    {
        $currentTrait = parent::current();
        $bonus = $this->morph->searchAttributeBonus($currentTrait->getAbbrev());

        return new TraitRoll($currentTrait, $bonus);
    }

}
