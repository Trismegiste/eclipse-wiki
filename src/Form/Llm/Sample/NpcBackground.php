<?php

namespace App\Form\Llm\Sample;

use App\Entity\Vertex;
use App\Form\Llm\LlmContentInfo;
use App\Form\Llm\PromptType;
use App\Service\Ollama\ParameterizedPrompt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NpcBackground extends AbstractType implements LlmContentInfo
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('title', TextType::class, ['attr' => ['placeholder' => 'nom ou surnom']])
                ->add('role', TextType::class, ['attr' => ['placeholder' => 'un rôle']])
                ->add('location', TextType::class, ['attr' => ['placeholder' => 'un lieu']])
                ->add('gender', ChoiceType::class, [
                    'choices' => [
                        'un homme' => 'un homme',
                        'une femme' => 'une femme'
                    ]
                ])
                ->add('job', TextType::class, ['attr' => ['placeholder' => 'son travail']])
                ->add('speciality', TextType::class, ['attr' => ['placeholder' => 'sa spécialité']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('prompt_template', "Dans le contexte précedemment décris, rédige un historique pour le personnage nommé {{title}}. " .
                "Ce personnage est {{role}} qui vit sur {{location}}. " .
                "C'est {{gender}}, {{job}}, spécialisé dans {{speciality}}. " .
                "Cet historique doit comporter 7 points. Un des 7 points doit comporter un evenement tragique et un autre point doit concerner sa famille. Pour chaque point, précise le lieu dans le système solaire.");
    }

    public function getParent(): string
    {
        return PromptType::class;
    }

    public static function getContentTitle(): string
    {
        return 'Background';
    }

    public static function initializeWithVertex(ParameterizedPrompt $param, Vertex $vertex): void
    {
        $splitted = explode(' ', $vertex->getTitle());
        $param->param['title'] = $splitted[0];
    }

}
