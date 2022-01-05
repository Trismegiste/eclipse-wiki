<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use Symfony\Component\Form\FormView;

/**
 * Tools for editing Form Type after its building
 */
trait FormTypeUtils
{

    private function moveChildAtBegin(FormView $view, string $key): void
    {
        $field = $view->children[$key];
        unset($view->children[$key]);
        $view->children = array_merge([$key => $field], $view->children);
    }

    private function moveChildAtEnd(FormView $view, string $key): void
    {
        $field = $view->children[$key];
        unset($view->children[$key]);
        $view->children[$key] = $field;
    }

    private function changeAttribute(FormView $view, string $key, string $attr, $value): void
    {
        $view[$key]->vars['attr'][$attr] = $value;
    }

    private function changeLabel(FormView $view, string $key, string $label): void
    {
        $view[$key]->vars['label'] = $label;
    }

}
