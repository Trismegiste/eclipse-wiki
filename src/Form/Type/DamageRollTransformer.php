<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\DamageRoll;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * string to DamageRoll
 */
class DamageRollTransformer implements DataTransformerInterface
{

    public function transform($value)
    {
        if (is_null($value)) {
            return '';
        }

        if (!$value instanceof DamageRoll) {
            throw new TransformationFailedException(json_encode($value) . ' is not a DamageRoll');
        }

        return (string) $value;
    }

    public function reverseTransform($value): ?DamageRoll
    {
        if (!$value) {
            return null;
        }

        return DamageRoll::createFromString($value);
    }

}
