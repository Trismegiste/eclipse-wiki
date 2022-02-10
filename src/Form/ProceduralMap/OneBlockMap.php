<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\ProceduralMap;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Trismegiste\MapGenerator\Procedural\CellularAutomaton;
use Trismegiste\MapGenerator\Procedural\SpaceStation;

/**
 * Form to generate a procedural map for one building block
 */
class OneBlockMap extends MapRecipe
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
        $gen->set($side / 2, $side / 2, 1);

        return $gen;
    }

}
