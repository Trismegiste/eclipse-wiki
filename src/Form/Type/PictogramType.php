<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of PictogramType
 *
 * @author florent
 */
class PictogramType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', [
            'processor' => 'processor'
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

}
