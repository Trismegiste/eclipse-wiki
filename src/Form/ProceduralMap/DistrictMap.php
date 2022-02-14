<?php

/*
 * eclipse-wiki
 */

namespace App\Form\ProceduralMap;

use App\Form\FormTypeUtils;
use App\MapLayer\RoomColor;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Trismegiste\MapGenerator\Procedural\CellularAutomaton;
use Trismegiste\MapGenerator\Procedural\SpaceStation;
use Trismegiste\MapGenerator\RpgMap;

/**
 * Form to generate a procedural map for a district
 */
class DistrictMap extends MapRecipe
{

    use FormTypeUtils;

    const colors = ['blue', 'green', 'yellow'];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('sizePerBlock', IntegerType::class, ['data' => 23])
                ->add('blockCount', IntegerType::class, ['data' => 5])
        ;
        parent::buildForm($builder, $options);

        $builder->add('color1', IntegerType::class, ['data' => 3, 'label_attr' => ['data-fill' => self::colors[0]]])
                ->add('color2', IntegerType::class, ['data' => 1, 'label_attr' => ['style' => 'color: ' . self::colors[1]]])
                ->add('color3', IntegerType::class, ['data' => 1, 'label_attr' => ['style' => 'color: ' . self::colors[2]]]);
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

    protected function stackAdditionalLayers(RpgMap $map, CellularAutomaton $cell, array $param): void
    {
        $coloring = new RoomColor($cell);
        $coloring->generate([
            self::colors[0] => $param['color1'],
            self::colors[1] => $param['color2'],
            self::colors[2] => $param['color3']
        ]);
        $map->appendLayer($coloring);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->moveChildAtEnd($view, 'openPopUp');
        $this->moveChildAtEnd($view, 'map_name');
        $this->moveChildAtEnd($view, 'writeMap');
    }

}
