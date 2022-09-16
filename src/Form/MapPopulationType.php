<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\MapConfig;
use App\Voronoi\MapBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var MapConfig $config */
            $config = $event->getData();
            $form = $event->getForm();

            $map = $this->builder->create($config);
            // adding fields according to statistics of the current map
            $stats = $map->getStatistics();
            foreach ($stats as $key => $tileCfg) {
                $form->add($key, Type\TileNpcConfigType::class, ['property_path' => "tilePopulation[$key]"]);
            }
        })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', MapConfig::class);
    }

}
