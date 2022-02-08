<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\ProceduralMap;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Trismegiste\MapGenerator\Procedural\DoorLayer;
use Trismegiste\MapGenerator\Procedural\FogOfWar;
use Trismegiste\MapGenerator\Procedural\NpcPopulator;
use Trismegiste\MapGenerator\Procedural\SpaceStation;
use Trismegiste\MapGenerator\RpgMap;

/**
 * Form to generate a procedural map for one building block
 */
class OneBlockMap extends AbstractType implements DataMapperInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('seed', IntegerType::class)
            ->add('side', IntegerType::class)
            ->add('iteration', IntegerType::class)
            ->add('capping', IntegerType::class)
            ->add('divide', IntegerType::class)
            ->add('blurry', CheckboxType::class, ['required' => false])
            ->add('one_more', CheckboxType::class, ['required' => false])
            ->add('npc', IntegerType::class)
            ->add('openPopUp', SubmitType::class)
            ->add('writeMap', SubmitType::class, ['attr' => ['class' => 'button-write']])
            ->setMethod('GET')
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data', [
            'seed' => random_int(1000, 9999),
            'side' => 20,
            'iteration' => 10,
            'divide' => 1,
            'capping' => 5,
            'npc' => 0
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

    public function mapFormsToData(\Traversable $forms, &$viewData)
    {
        /** @var FormInterface[] $param */
        $param = iterator_to_array($forms);

        srand($param['seed']->getData());
        $side = $param['side']->getData();

        $gen = new SpaceStation($side);
        $gen->set($side / 2, $side / 2, 1);

        for ($idx = 0; $idx < $param['iteration']->getData(); $idx++) {
            $gen->iterate();
        }
        $gen->roomIterationCapping($param['capping']->getData());
        $gen->roomIterationDivide($param['divide']->getData());

        if ($param['blurry']->getData()) {
            $gen->blurry();
        }

        if ($param['one_more']->getData()) {
            $gen->iterate();
        }

        $door = new DoorLayer($gen);
        $door->findDoor();
        $pop = new NpcPopulator($gen);
        $pop->generate($param['npc']->getData());
        $fog = new FogOfWar($gen);

        $viewData = new RpgMap($gen);
        $viewData->appendLayer($door);
        $viewData->appendLayer($pop);
        $viewData->appendLayer($fog);
    }

    public function getBlockPrefix()
    {
        return 'mapgen';
    }

}
