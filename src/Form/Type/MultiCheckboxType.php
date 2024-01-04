<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ChoiceType with checkboxes
 */
class MultiCheckboxType extends AbstractType
{

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => true,
            'expanded' => false
        ]);
    }

}
