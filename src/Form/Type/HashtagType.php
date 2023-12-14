<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Listing of Hashtags with default value to import or not
 */
class HashtagType extends AbstractType
{

    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['hashtag'] = $options['default_hashtag'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('default_hashtag');
    }

}
