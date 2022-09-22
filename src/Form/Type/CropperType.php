<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

/**
 * A File upload with cropping widget
 */
class CropperType extends AbstractType
{

    public function getParent()
    {
        return FileType::class;
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
            'attr' => ['x-on:change' => 'readFile($el)'],
            'help' => '(ou Ctrl-V)'
        ]);
    }

}
