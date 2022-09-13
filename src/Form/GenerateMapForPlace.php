<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\MapConfig;
use App\Entity\Place;
use App\Service\Storage;
use App\Voronoi\MapBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Generate a new map for a Place
 * (also generates a default name for a new Place)
 */
class GenerateMapForPlace extends AbstractType implements \Symfony\Component\Form\DataMapperInterface
{

    protected MapBuilder $builder;
    protected Storage $storage;

    public function __construct(MapBuilder $builder, Storage $storage)
    {
        $this->builder = $builder;
        $this->storage = $storage;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Place::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('store_battlemap', SubmitType::class)
                ->setDataMapper($this)
                ->setMethod('PATCH');
    }

    public function mapDataToForms($viewData, \Traversable $forms)
    {
        // nothing to edit
    }

    public function mapFormsToData(\Traversable $forms, &$place)
    {
        if (is_null($place)) {
            throw new \LogicException('Place cannot be NULL');
        }

        if (!$place instanceof Place) {
            throw new \Symfony\Component\Form\Exception\UnexpectedTypeException($viewData, Place::class);
        }

        $map = $this->builder->create($place->voronoiParam);
        $filename = 'map-' . $place->getPk() . '.svg';
        $this->builder->save($map, join_paths($this->storage->getRootDir(), $filename));
        $place->battleMap = $filename;
    }

}
