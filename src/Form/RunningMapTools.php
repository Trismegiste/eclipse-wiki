<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The tool box for running a battlemap
 */
class RunningMapTools extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fog_of_war', CheckboxType::class, [
                    'attr' => [
                        'x-model' => "fogEnabled",
                        'x-on:change' => "toggleFog"
                    ]
                ])
                ->add('search_legend', TextType::class, [
                    'attr' => [
                        'x-model' => "legendFilter",
                        'x-on:blur' => "searchLegend",
                        'class' => "pure-input-1-2"
                    ]
                ])
                ->add('character_add', Type\NpcChoiceType::class, [
                    'placeholder'=>'-- CHOOSE_NPC --',
                    'attr' => [
                        'x-model' => "newCharacter",
                        'x-on:change' => "characterAdd"
                    ]
                ])
                ->add('broadcast', ButtonType::class, [
                    'attr' => [
                        'x-on:click' => "playerBroadcast"
                    ]
                ])
        ;
    }

}
