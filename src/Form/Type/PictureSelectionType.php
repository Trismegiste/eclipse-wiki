<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of PictureSelectionType
 *
 * @author florent
 */
class PictureSelectionType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('picture', LocalPictureType::class)
                ->add('selected', CheckboxType::class, ['required' => false])
        ;
    }

}
