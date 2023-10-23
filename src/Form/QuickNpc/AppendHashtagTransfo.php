<?php

/*
 * eclipse-wiki
 */

namespace App\Form\QuickNpc;

use App\Entity\Transhuman;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Inject a random selection of hashtags
 */
class AppendHashtagTransfo implements DataTransformerInterface
{

    const hashtagExtract = 4;

    public function reverseTransform(mixed $value): mixed
    {
        if (is_null($value)) {
            return null;
        }
        if (!$value instanceof Transhuman) {
            throw new TransformationFailedException(get_class($value) . " is not Transhuman");
        }

        $defaultTags = explode(' ', $value->getDefaultHashtag());
        shuffle($defaultTags);
        $value->hashtag = implode(' ', array_splice($defaultTags, 0, self::hashtagExtract));

        return $value;
    }

    public function transform(mixed $value): mixed
    {
        return $value;
    }

}
