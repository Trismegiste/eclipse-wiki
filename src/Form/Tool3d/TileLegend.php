<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Tool3d;

use App\Form\Type\PictogramType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
                ->add('legend', TextType::class, [
                    'attr' => ['x-model' => 'cellInfo.legend'],
                    'required' => false
                ])
                ->add('pictogram', PictogramType::class, [
                    'attr' => ['x-model' => 'cellInfo.pictogram'],
                    'placeholder' => '-----------------',
                    'required' => false
                ])
                ->add('markerColor', ColorType::class, [
                    'attr' => ['x-model' => 'cellInfo.markerColor'],
                    'required' => false
                ])
                ->add('set_legend', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('attr', ['x-on:submit' => "setLegend"]);
    }

}
