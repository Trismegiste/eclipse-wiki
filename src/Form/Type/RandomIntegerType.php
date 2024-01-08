<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * An IntegerType with a random button
 */
class RandomIntegerType extends AbstractType
{

    public function getParent(): ?string
    {
        return IntegerType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('attr', [
            'class' => 'pure-input-1',
            'x-model.fill' => 'result'
        ]);
    }

}
