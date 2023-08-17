<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use RuntimeException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Trismegiste\Strangelove\MongoDb\Repository;
use Trismegiste\Strangelove\MongoDb\Root;

/**
 * Tranformer primary key to document
 */
class AjaxCompleteTransfo implements DataTransformerInterface
{

    public function __construct(protected Repository $repository)
    {
        
    }

    public function reverseTransform(mixed $value): mixed
    {
        // no pk ?
        if (empty($value)) {
            return null;
        }

        try {
            return $this->repository->load($value);
        } catch (RuntimeException $ex) {
            throw new TransformationFailedException("Document '$value' not found", 404, $ex);
        }
    }

    public function transform(mixed $value): mixed
    {
        return null;
    }

}
