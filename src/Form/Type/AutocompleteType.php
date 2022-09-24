<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of AutocompleteType
 *
 * @author florent
 */
class AutocompleteType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('choices');
        $resolver->setAllowedTypes('choices', 'iterable');
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['choices'] = array_values(iterator_to_array($options['choices']));
    }

}
