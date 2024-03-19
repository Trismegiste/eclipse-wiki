<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use SplFileInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of LocalPictureType
 *
 * @author florent
 */
class LocalPictureType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', SplFileInfo::class);
    }

    public function getParent(): string
    {
        return TextType::class;
    }

}
