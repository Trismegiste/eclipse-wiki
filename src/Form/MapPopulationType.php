<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\MapConfig;
use App\Voronoi\MapBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Traversable;

/**
 * Parameters for populating a battlemap
 */
class MapPopulationType extends AbstractType implements DataMapperInterface
{

    protected MapBuilder $builder;

    public function __construct(MapBuilder $mapBuilder)
    {
        $this->builder = $mapBuilder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                    /** @var MapConfig $config */
                    $config = $event->getData();
                    $form = $event->getForm();

                    $map = $this->builder->create($config);
                    $stats = $map->getStatistics();
                    foreach ($stats as $key => $tileCfg) {
                        $form->add($key, Type\TileNpcConfigType::class);
                    }
                })
                ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', MapConfig::class);
    }

    public function mapDataToForms($viewData, Traversable $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!is_array($viewData->tilePopulation)) {
            throw new UnexpectedTypeException($viewData->tilePopulation, 'array');
        }

        /** @var FormInterface $field */
        foreach ($forms as $key => $field) {
            if (key_exists($key, $viewData->tilePopulation)) {
                $field->setData($viewData->tilePopulation[$key]);
            }
        }
    }

    public function mapFormsToData(Traversable $forms, &$viewData)
    {
        /** @var MapConfig $viewData */
        $viewData->tilePopulation = [];
        /** @var FormInterface $field */
        foreach ($forms as $key => $field) {
            $val = $field->getData();
            if (!empty($val) && ($val->tilePerNpc > 0) && !empty($val->npcTitle)) {
                $viewData->tilePopulation[$key] = $val;
            }
        }
    }

}
