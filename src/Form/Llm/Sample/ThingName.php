<?php

/*
 * Eclipse Wiki
 */

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

/**
 * Description of NameGenerator
 *
 * @author florent
 */
class ThingName extends AbstractType implements LlmContentInfo
{

    const nameStyle = [
        'mystérieux',
        'accrocheurs',
        'technologiques',
        'exotiques',
        'ésotériques'
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('style', ChoiceType::class, [
                    'choices' => array_combine(self::nameStyle, self::nameStyle)
                ])
                ->add('category', TextType::class, ['attr' => ['placeholder' => 'une catégorie']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('prompt_template', <<<PROMPT
Dans le contexte précedemment décris, donne moi 10 noms originaux et {{style}} de {{category}}.
Réponds sous forme de tableau JSON où chaque élément du tableau est un objet qui contient la clef "name".
Voici un exemple de tableau : [{"name":"Yolo's bar"},{"name":"Discovery One"}];
PROMPT
        );
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
        
    }

}
