<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Tool3d;

use App\Entity\LegendSpot;
use App\Entity\Place;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Tool for selecting a spotted legend in a Place content
 */
class SpotSelect extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('spot', ChoiceType::class, [
                    'choices' => $options['place']->extractLegendSpot(),
                    'attr' => [
                        'x-model' => 'legendSpot',
                        'x-on:change' => 'jumpToSpot',
                    ],
                    'placeholder' => '---------',
                    'choice_label' => function (?LegendSpot $obj) {
                        return $obj?->getTitle();
                    },
                    'choice_value' => function (?LegendSpot $obj) {
                        return $obj?->getIndex();
                    }
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('place');
        $resolver->setAllowedTypes('place', Place::class);
        $resolver->setDefault('attr', ['x-data' => '{legendSpot:null}']);
    }

}
