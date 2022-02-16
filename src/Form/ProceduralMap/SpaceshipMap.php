<?php

/*
 * eclipse-wiki
 */

namespace App\Form\ProceduralMap;

use App\MapLayer\AxialSymmetry;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Trismegiste\MapGenerator\Procedural\CellularAutomaton;
use Trismegiste\MapGenerator\Procedural\SpaceStation;
use Trismegiste\MapGenerator\RpgMap;

/**
 * Map for a spaceship
 */
class SpaceshipMap extends MapRecipe
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('side', IntegerType::class, ['data' => 35]);
        parent::buildForm($builder, $options);
    }

    protected function createAutomaton(array $param): CellularAutomaton
    {
        $side = $param['side'];
        $gen = new SpaceStation($side);
        for ($delta = -6; $delta <= 0; $delta++) {
            $gen->set($side / 2, $side / 2 + $delta, 10);
        }

        $gen->set($side / 2 - 4, $side / 2 + 6, 10);
        $gen->set($side / 2 + 4, $side / 2 + 6, 10);

        return $gen;
    }

    protected function stackAdditionalLayers(RpgMap $map, CellularAutomaton $cell, array $param): void
    {
        $symetry = new AxialSymmetry($cell);
        $symetry->duplicate();
        $map->appendLayer($symetry);
    }

}
