<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Llm;

use App\Service\Ollama\ParameterizedPrompt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
        $builder
                ->add('generate', SubmitType::class, [
                    'attr' => ['class' => 'button-continue'],
                    'priority' => -1000
                ])
                ->setDataMapper(new PromptMapper($options['prompt_template'], $this->twig))
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['prompt_template'] = preg_replace('#\{\{([^\}]+)\}\}#', '{{form_widget(form.$1)}}', $options['prompt_template']);
    }

}
