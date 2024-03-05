<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Inventory of one Morph
 */
class MorphInventory extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('morph', TextType::class, [
                    'attr' => [
                        'x-model' => 'entry.morph',
                        'placeholder' => 'Morph',
                        'class' => 'pure-input-1'
                    ]
                ])
                ->add('stock', IntegerType::class, [
                    'attr' => [
                        'x-model' => 'entry.stock',
                        'placeholder' => 'Stock',
                        'class' => 'pure-input-1'
                    ]
                ])
                ->add('scarcity', IntegerType::class, [
                    'attr' => [
                        'x-model' => 'entry.scarcity',
                        'placeholder' => 'Dispo',
                        'class' => 'pure-input-1'
                    ]
                ])
        ;
    }

}
