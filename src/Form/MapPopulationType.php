<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\MapConfig;
use App\Voronoi\MapBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Parameters for populating a battlemap
 */
class MapPopulationType extends AbstractType
{

    protected MapBuilder $builder;

    public function __construct(MapBuilder $mapBuilder)
    {
        $this->builder = $mapBuilder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tilePopulation', CollectionType::class, [
            'entry_type' => Type\TileNpcConfigType::class,
            'entry_options' => ['required' => false],
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => function (\App\Entity\TileNpcConfig $cfg = null) {
                return is_null($cfg);
            },
            'prototype' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', MapConfig::class);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->isSubmitted()) {
            return; // @todo ugly patch
        }

        /** @var MapConfig $config */
        $config = $form->getViewData();
        $data = $config->tilePopulation;
        $map = $this->builder->create($config);
        $stats = $map->getStatistics();

        // adding fields according to statistics of the current map
        foreach ($stats as $key => $unused) {
            if (!key_exists($key, $data)) {
                $data[$key] = null;
            }
        }

        $form['tilePopulation']->setData($data);
    }

}
