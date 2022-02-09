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
 * Form to generate a procedural map for streets
 */
class StreetMap extends MapRecipe
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('streetWidth', IntegerType::class)
                ->add('streetCount', IntegerType::class)
        ;
        parent::buildForm($builder, $options);
    }

    protected function createAutomaton(array $param): CellularAutomaton
    {
        $side = $param['streetWidth'] * $param['streetCount'];
        $gen = new SpaceStation($side);
        for ($col = $param['streetWidth'] / 2; $col < $side; $col += $param['streetWidth']) {
            for ($row = 1; $row < $side - 1; $row++) {
                $gen->set($col, $row);
            }
        }

        return $gen;
    }

}
