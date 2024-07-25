<?php

/*
 * eclipse-wiki
 */

namespace App\Ollama;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Abstract parameterized prompt form type
 */
class PromptType extends AbstractType
{

    public function __construct(protected Environment $twig)
    {
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('prompt_template');
        $resolver->setAllowedTypes('prompt_template', 'string');
        $resolver->setDefault('data_class', ParameterizedPrompt::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setDataMapper(new PromptMapper($options['prompt_template'], $this->twig));
    }

    public function finishView(\Symfony\Component\Form\FormView $view, \Symfony\Component\Form\FormInterface $form, array $options): void
    {
        $view->vars['prompt_template'] = preg_replace('#\{\{([^\}]+)\}\}#', '{{form_widget(form.$1)}}', $options['prompt_template']);
    }

}
