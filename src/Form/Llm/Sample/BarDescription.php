<?php

namespace App\Form\Llm\Sample;

use App\Entity\Vertex;
use App\Form\Llm\LlmContentInfo;
use App\Form\Llm\PromptType;
use App\Service\Ollama\ParameterizedPrompt;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BarDescription extends AbstractType implements LlmContentInfo
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('title', TextType::class, ['attr' => ['placeholder' => 'un nom']])
                ->add('ambience', TextType::class, ['attr' => ['placeholder' => 'une ambiance']])
                ->add('crowd', TextType::class, ['attr' => ['placeholder' => 'un type de clients']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('prompt_template',
                "Dans le contexte précedemment décris, rédige une description en 15 lignes d'un bar nommé \"{{title}}\". " .
                "Ce bar est plutôt {{ambience}} et il est fréquenté par {{crowd}}. " .
                "Ajoute une description succinte du barman et des serveuses. Décris les alcools qu'on peut y boire. " .
                "Décris la musique qu'on peut y écouter et raconte quelques faits notables.");
    }

    public function getParent(): string
    {
        return PromptType::class;
    }

    public static function getContentTitle(): string
    {
        return 'Description';
    }

    public static function initializeWithVertex(ParameterizedPrompt $param, Vertex $vertex): void
    {
        $param->param['title'] = $vertex->getTitle();
    }

}
