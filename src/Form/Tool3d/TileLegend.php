<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Tool3d;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Widget for editing legend on tile from a battlemap
 */
class TileLegend extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('legend', TextareaType::class, [
                    'attr' => ['x-model' => 'cellInfo.legend']
                ])
                ->add('name', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('attr', ['x-on:submit' => "setLegend"]);
    }

}
