<?php

/*
 * eclipse-wiki
 */

namespace App\Form\ProceduralMap;

use App\MapLayer\QuarterSymmetry;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Trismegiste\MapGenerator\Procedural\CellularAutomaton;
use Trismegiste\MapGenerator\Procedural\SpaceStation;
use Trismegiste\MapGenerator\RpgMap;

/**
 * Generator for Station
 */
class StationMap extends MapRecipe
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('side', IntegerType::class);
        parent::buildForm($builder, $options);
    }

    protected function createAutomaton(array $param): CellularAutomaton
    {
        $side = $param['side'];
        $gen = new SpaceStation($side);

        $gen->set($side / 2, $side / 2, 10);
        $gen->set($side / 2 + 4, $side / 2 + 4, 5);
        $gen->set($side / 2 - 4, $side / 2 + 4, 5);
        $gen->set($side / 2 + 4, $side / 2 - 4, 5);
        $gen->set($side / 2 - 4, $side / 2 - 4, 5);
        
        return $gen;
    }

    protected function stackAdditionalLayers(RpgMap $map, CellularAutomaton $cell, array $param): void
    {
        $symetry = new QuarterSymmetry($cell);
        $symetry->duplicate();
        $map->appendLayer($symetry);
    }

}
