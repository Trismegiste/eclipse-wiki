<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * A text area widget with wikitext autocomplete link
 */
class WikitextType extends AbstractType
{

    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr'] = array_merge($view->vars['attr'], [
            'x-model' => "content",
            'x-on:keyup.debounce.100ms' => "editKeyUp",
            'x-on:click' => "open=false",
            'x-ref' => "editor"
        ]);
    }

}
