<?php

namespace App\Ollama;

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
                ->add('location', TextType::class)
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
        $resolver->setDefault('prompt_template', "Écris un historique sur 7 points d'{{role}} qui vit sur {{location}}. " .
                "C'est {{gender}}, {{job}}, spécialisé dans {{speciality}}. " .
                "Un des 7 points doit comporter un evenement tragique et un autre point doit concerner sa famille. Pour chaque point, précise le lieu dans le système solaire.");
    }

    public function getParent(): string
    {
        return PromptType::class;
    }

}
