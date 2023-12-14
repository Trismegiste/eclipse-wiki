<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Tool3d;

use App\Form\Type\NpcChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Widget for adding a NPC on tile from a battlemap
 */
class TileNpc extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('npc', NpcChoiceType::class, [
                    'placeholder' => '---------',
                    'attr' => ['x-model' => 'selectedNpc']
                ])
                ->add('append', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('attr', [
            'x-on:submit' => "appendNpc",
            'x-show' => 'characterCard == null'
        ]);
    }

}
