<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Attribute;
use App\Repository\AttributeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Value for an Attribute
 */
class AttributeType extends AbstractType
{

    protected $repository;

    public function __construct(AttributeProvider $repo)
    {
        $this->repository = $repo;
    }

    public function getParent()
    {
        return SaWoTraitType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('provider', $this->repository);
        $resolver->setDefault('data_class', Attribute::class);
    }

}
