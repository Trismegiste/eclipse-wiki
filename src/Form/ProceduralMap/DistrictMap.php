<?php

/*
 * eclipse-wiki
 */

namespace App\Form\ProceduralMap;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Trismegiste\MapGenerator\Procedural\CellularAutomaton;
use Trismegiste\MapGenerator\Procedural\SpaceStation;

/**
 * Form to generate a procedural map for a district
 */
class DistrictMap extends MapRecipe
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('sizePerBlock', IntegerType::class)
                ->add('blockCount', IntegerType::class)
        ;
        parent::buildForm($builder, $options);
    }

    protected function createAutomaton(array $param): CellularAutomaton
    {
        $side = $param['sizePerBlock'] * $param['blockCount'];
        $gen = new SpaceStation($side);
        for ($col = $param['sizePerBlock'] / 2; $col < $side - 1; $col += $param['sizePerBlock']) {
            for ($row = $param['sizePerBlock'] / 2; $row < $side - 1; $row += $param['sizePerBlock']) {
                $gen->set($col, $row);
            }
        }

        return $gen;
    }

}
