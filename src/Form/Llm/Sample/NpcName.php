<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Llm\Sample;

use App\Form\Llm\LlmListingInfo;
use App\Form\Llm\PromptType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Generation of listing of NPC names
 */
class NpcName extends AbstractType implements LlmListingInfo
{

    const nameStyle = [
        'chinois',
        'japonais',
        'indiens',
        'coréens',
        'américains',
        'anglais',
        'français',
        'espagnols',
        'allemands'
    ];
    const gender = [
        'masculins',
        'féminins'
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('style', ChoiceType::class, [
                    'choices' => array_combine(self::nameStyle, self::nameStyle)
                ])
                ->add('gender', ChoiceType::class, [
                    'choices' => array_combine(self::gender, self::gender)
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('prompt_template', <<<PROMPT
Dans le contexte précedemment décris, donne moi 20 noms et prénoms {{style}} originaux et rares pour des personnages {{gender}}.
Réponds sous forme de tableau JSON où chaque élément du tableau est un objet qui contient 2 clefs "firstname" et "lastname".
Voici un exemple de tableau avec un élément
[{"firstname":"Takeshi", "lastname":"Kovacs"}];
PROMPT
        );
    }

    public function getParent(): string
    {
        return PromptType::class;
    }

    static public function getEntryDumpJs(): string
    {
        return "entry.firstname + ' ' + entry.lastname";
    }

}
