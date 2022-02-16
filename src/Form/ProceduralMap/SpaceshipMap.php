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
 * Map for a sapceship
 */
class SpaceshipMap extends MapRecipe
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('side', IntegerType::class, ['data' => 25]);
        parent::buildForm($builder, $options);
    }

    protected function createAutomaton(array $param): CellularAutomaton
    {
        $side = $param['side'];
        $gen = new SpaceStation($side);
        for ($delta = -6; $delta <= 0; $delta++) {
            $gen->set($side / 2, $side / 2 + $delta, 1);
        }

        $gen->set($side / 2 - 4, $side / 2 + 8, 1);
        $gen->set($side / 2 + 4, $side / 2 + 8, 1);

        return $gen;
    }

    protected function stackAdditionalLayers(\Trismegiste\MapGenerator\RpgMap $map, CellularAutomaton $cell, array $param): void
    {
        $symetry = new \App\MapLayer\AxialSymmetry($cell);
        $symetry->duplicate();
        $map->appendLayer($symetry);
    }

}
