<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Yaml\Yaml;

/**
 * A text field with random capabilities
 */
class RandomNameType extends AbstractType
{

    protected $category;

    public function __construct(string $yaml)
    {
        $this->category = Yaml::parseFile($yaml);
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['category'] = $this->category;
    }

}
