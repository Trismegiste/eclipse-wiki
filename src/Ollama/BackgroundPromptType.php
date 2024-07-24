<?php

namespace App\Ollama;

use App\Ollama\Prompt\Background;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BackgroundPromptType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('role', TextType::class)
                ->add('gender', ChoiceType::class, [
                    'choices' => [
                        'un homme' => 'un homme',
                        'une femme' => 'une femme'
                    ]
                ])
                ->add('job', TextType::class)
                ->add('speciality', TextType::class)
                ->add('generate', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Background::class);
    }

}
