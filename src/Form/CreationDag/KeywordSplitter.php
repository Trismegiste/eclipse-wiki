<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Prompt query splitter
 */
class KeywordSplitter implements DataTransformerInterface
{

    public function reverseTransform(mixed $value): mixed
    {
        if (empty($value)) {
            return [];
        }
        return explode(' ', $value);
    }

    public function transform(mixed $value): mixed
    {
        if (is_null($value)) {
            return '';
        }

        return implode(' ', $value);
    }

}
