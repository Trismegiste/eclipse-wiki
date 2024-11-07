<?php

namespace App\Form\Llm\Sample;

use App\Entity\Vertex;
use App\Form\Llm\PromptType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FreePrompt extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('free', TextareaType::class, ['attr' => ['class' => 'pure-input-1', 'rows' => 6]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('prompt_template', "Dans le contexte précedemment décrit, rédige {{free}}");
    }

    public function getParent(): string
    {
        return PromptType::class;
    }

}
