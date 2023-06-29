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

    public function __construct(array $traitList, protected array $bonus = [])
    {
        parent::__construct($traitList);
    }

    public function current(): mixed
    {
        $currentTrait = parent::current();
        $key = mb_substr(mb_strtoupper($currentTrait->getName()), 0, 3);
        $bonus = (key_exists($key, $this->bonus)) ? $this->bonus[$key] : null;

        return new TraitRoll($currentTrait, $bonus);
    }

}
