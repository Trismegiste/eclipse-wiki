<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transform an array of key (from a ChoiceType) to hashmap with fixed bonus
 */
class KeyBonusTransfo implements DataTransformerInterface
{

    public function __construct(protected int $fixedBonus = 1)
    {
        
    }

    public function reverseTransform(mixed $value): mixed
    {
        $bonusModel = [];
        foreach ($value as $trait) {
            $bonusModel[$trait] = $this->fixedBonus;
        }

        return $bonusModel;
    }

    public function transform(mixed $value): mixed
    {
        $choiceView = [];

        if (is_array($value)) {
            foreach ($value as $trait => $bonus) {
                $choiceView[$trait] = $trait;
            }
        }

        return $choiceView;
    }

}
