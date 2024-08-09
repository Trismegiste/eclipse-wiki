<?php

/*
 * eclipse wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Autocomplete with AlpineJS
 */
class AlpineCompleteType extends AbstractType
{

    public function getParent(): string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('ajax');
        $resolver->setAllowedTypes('ajax', 'string');
        $resolver->setDefault('attr', [
            'x-model.prefill' => 'content',
            'x-on:keyup' => 'fieldKeyUp',
            'x-on:input.debounce.100ms' => 'userInput',
            'autocomplete' => 'off',
            'x-ref' => 'textfield'
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['ajax'] = $options['ajax'];
    }

}
