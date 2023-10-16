<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

/**
 * Description of KeywordSplitter
 *
 * @author trismegiste
 */
class KeywordSplitter implements \Symfony\Component\Form\DataTransformerInterface
{

    public function reverseTransform(mixed $value): mixed
    {
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
