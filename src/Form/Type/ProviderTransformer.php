<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\Indexable;
use App\Repository\GenericProvider;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * foreign key transformer
 */
class ProviderTransformer implements DataTransformerInterface
{

    protected $provider;

    public function __construct(GenericProvider $pro)
    {
        $this->provider = $pro;
    }

    public function reverseTransform($value): ?Indexable
    {
        if (empty($value)) {
            return null;
        }

        return $this->provider->findOne($value);
    }

    public function transform($value): string
    {
        if (null === $value) {
            return '';
        }
        if (!$value instanceof Indexable) {
            throw new TransformationFailedException();
        }

        return $value->getUId();
    }

}
