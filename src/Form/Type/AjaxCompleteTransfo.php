<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\DataTransformerInterface;
use Trismegiste\Strangelove\MongoDb\Repository;

/**
 * Description of AjaxCompleteTransfo
 *
 * @author trismegiste
 */
class AjaxCompleteTransfo implements DataTransformerInterface
{

    public function __construct(protected Repository $repository)
    {
        
    }

    public function reverseTransform(mixed $value): mixed
    {
        return $this->repository->load($value);
    }

    public function transform(mixed $value): mixed
    {
        return null;
    }

}
