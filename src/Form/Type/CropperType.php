<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A File upload with cropping widget
 */
class CropperType extends AbstractType
{

    public function getParent(): ?string
    {
        return FileType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'attr' => ['x-on:change' => 'readFile($el)'],
            'help' => '(ou Ctrl-V)',
            'avatar_size' => 500,
            'default_picture' => null
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['avatar_size'] = $options['avatar_size'];
        $view->vars['default_picture'] = $options['default_picture'];
    }

}
