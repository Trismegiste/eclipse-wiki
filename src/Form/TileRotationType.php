<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\HexagonalTile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Enable the 6 rotations on a tile
 */
class TileRotationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('rotation', CollectionType::class, [
                    'entry_type' => CheckboxType::class,
                    'entry_options' => [
                        'required' => false
                    ]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', HexagonalTile::class);
    }

}
