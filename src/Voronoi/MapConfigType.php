<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * All parameters for generating a Voronoi map
 */
class MapConfigType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('side', NumberType::class)
                ->add('avgTilePerRoom', NumberType::class)
                ->add('minRoomSize', NumberType::class)
                ->add('maxNeighbour', NumberType::class)
                ->add('generate', SubmitType::class);
    }

}
