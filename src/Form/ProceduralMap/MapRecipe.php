<?php

/*
 * eclipse-wiki
 */

namespace App\Form\ProceduralMap;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            ->add('seed', IntegerType::class)
            ->add('iteration', IntegerType::class)
            ->add('capping', IntegerType::class)
            ->add('divide', IntegerType::class)
            ->add('blurry', CheckboxType::class, ['required' => false])
            ->add('one_more', CheckboxType::class, ['required' => false])
            ->add('npc', IntegerType::class)
            ->add('openPopUp', SubmitType::class)
            ->add('map_name', TextType::class)
            ->add('writeMap', SubmitType::class, ['attr' => ['class' => 'button-write']])
            ->setMethod('GET')
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $seed = random_int(10000, 99999);
        $resolver->setDefault('data', [
            'seed' => $seed,
            'iteration' => 10,
            'divide' => 1,
            'capping' => 5,
            'npc' => 0,
            'map_name' => "map-$seed"
        ]);
    }

    public function mapDataToForms($viewData, \Traversable $forms)
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

    final public function mapFormsToData(\Traversable $forms, &$viewData)
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

        $door = new DoorLayer($gen);
        $door->findDoor();
        $pop = new NpcPopulator($gen);
        $pop->generate($param['npc']);
        $fog = new FogOfWar($gen);

        $viewData = new RpgMap($gen);
        $viewData->appendLayer($door);
        $viewData->appendLayer($pop);
        $this->stackAdditionalLayers($viewData, $gen, $param);
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
