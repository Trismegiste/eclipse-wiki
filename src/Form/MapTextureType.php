<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\MapConfig;
use App\Repository\TileProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for texturing a map
 */
class MapTextureType extends AbstractType
{

    protected TileProvider $provider;

    public function __construct(TileProvider $provider)
    {
        $this->provider = $provider;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', MapConfig::class);
        $resolver->setRequired('tileset');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('tileWeight', CollectionType::class, [
                    'entry_type' => IntegerType::class,
                    'entry_options' => ['required' => false],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'prototype_name' => 'default'
                ])
                ->add('minClusterPerTile', CollectionType::class, [
                    'entry_type' => IntegerType::class,
                    'entry_options' => ['required' => false],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'prototype_name' => 'default'
                ])
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $tileSet = $this->provider->getClusterSet($options['tileset']);

        $this->initializeCollection($form['tileWeight'], $tileSet);
        $this->initializeCollection($form['minClusterPerTile'], $tileSet);
    }

    private function initializeCollection(FormInterface $form, iterable $tileSet): void
    {
        $data = $form->getData();
        foreach ($tileSet as $tile) {
            if (!key_exists($tile->getKey(), $data)) {
                $data[$tile->getKey()] = null;
            }
        }
        $form->setData($data);
    }

}
