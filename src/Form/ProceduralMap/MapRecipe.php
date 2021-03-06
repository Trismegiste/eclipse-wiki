<?php

/*
 * eclipse-wiki
 */

namespace App\Form\ProceduralMap;

use App\Form\Type\PlaceChoiceType;
use App\Form\Type\RandomIntegerType;
use App\MapLayer\HexGrid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Traversable;
use Trismegiste\MapGenerator\Procedural\CellularAutomaton;
use Trismegiste\MapGenerator\Procedural\DoorLayer;
use Trismegiste\MapGenerator\Procedural\FogOfWar;
use Trismegiste\MapGenerator\Procedural\NpcPopulator;
use Trismegiste\MapGenerator\RpgMap;

/**
 * Generic recipe for building a map with cellular automaton
 */
abstract class MapRecipe extends AbstractType implements DataMapperInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('seed', RandomIntegerType::class, ['constraints' => [new NotBlank, new Positive()]])
                ->add('iteration', IntegerType::class, ['constraints' => [new NotBlank, new Positive()]])
                ->add('capping', IntegerType::class, ['constraints' => [new NotBlank, new Positive()]])
                ->add('divide', IntegerType::class, ['constraints' => [new NotBlank, new Positive()]])
                ->add('blurry', CheckboxType::class, ['required' => false, 'false_values' => [null, false, '0']])
                ->add('one_more', CheckboxType::class, ['required' => false, 'false_values' => [null, false, '0']])
                ->add('outsider', IntegerType::class, ['constraints' => [new NotBlank, new PositiveOrZero()]])
                ->add('insider', IntegerType::class, ['constraints' => [new NotBlank, new PositiveOrZero()]])
                ->add('runMap', SubmitType::class)
                ->add('place', PlaceChoiceType::class, [
                    'placeholder' => '-- Create New --',
                    'required' => false
                ])
                ->add('writeMap', SubmitType::class, ['attr' => ['class' => 'button-write']])
                ->setMethod('GET')
                ->setDataMapper($this);
    }

    public function mapDataToForms($viewData, Traversable $forms)
    {
        if (is_null($viewData)) {
            return;
        }

        if (!is_array($viewData)) {
            throw new UnexpectedTypeException($viewData, 'array or empty');
        }

        foreach ($forms as $key => $field) {
            if (key_exists($key, $viewData) && !is_null($viewData[$key])) {
                $field->setData($viewData[$key]);
            }
        }
    }

    final public function mapFormsToData(Traversable $forms, &$viewData)
    {
        /** @var FormInterface[] $param */
        $param = [];
        foreach ($forms as $key => $form) {
            $param[$key] = $form->getData();
        }

        srand($param['seed']);

        $gen = $this->createAutomaton($param);

        for ($idx = 0; $idx < $param['iteration']; $idx++) {
            $gen->iterate();
        }
        $gen->roomIterationCapping($param['capping']);
        $gen->roomIterationDivide($param['divide']);

        if ($param['blurry']) {
            $gen->blurry();
        }

        if ($param['one_more']) {
            $gen->iterate();
        }

        $viewData = new RpgMap($gen);
        $viewData->setParameters(['recipe' => get_class($this), 'form' => $param]);
        $this->stackAdditionalLayers($viewData, $gen, $param);

        $door = new DoorLayer($gen);
        $door->findDoor();
        $viewData->appendLayer($door);

        $viewData->appendLayer(new HexGrid($gen, 0.5));

        $pop = new NpcPopulator($gen);
        $pop->generate($param['outsider'], $param['insider'], 0.3);
        $viewData->appendLayer($pop);

        $fog = new FogOfWar($gen);
        $viewData->appendLayer($fog);
    }

    public function getBlockPrefix()
    {
        return 'mapgen';
    }

    abstract protected function createAutomaton(array $param): CellularAutomaton;

    protected function stackAdditionalLayers(RpgMap $map, CellularAutomaton $cell, array $param): void
    {
        
    }

}
