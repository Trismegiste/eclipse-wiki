<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Form\Type\PlaceChoiceType;
use App\Voronoi\HexaMap;
use App\Voronoi\MapBuilder;
use App\Entity\MapConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Generate a new map for a Place
 * (also generates a default name for a new Place)
 */
class GenerateMapForPlace extends AbstractType
{

    protected MapBuilder $builder;
    protected $translator;

    public function __construct(MapBuilder $builder, TranslatorInterface $translator)
    {
        $this->builder = $builder;
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('map_config');
        $resolver->setAllowedTypes('map_config', MapConfig::class);

        $resolver->setDefault('data_class', HexaMap::class);
        $resolver->setDefault('empty_data', function (Options $opt) {
            /** @var MapConfig $config */
            $config = $opt['map_config'];
            return function (FormInterface $form) use ($config) {
                return $this->builder->create($config);
            };
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $options['map_config'];

        $builder
                ->add('place', PlaceChoiceType::class, [
                    'placeholder' => '-- Create New --',
                    'required' => false,
                    'mapped' => false
                ])
                ->add('default_newname', HiddenType::class, [
                    'data' => sprintf('Map-%s %s-%d×%d %d',
                            $config->getTitle(),
                            $this->translator->trans($config->container->getName()),
                            $config->side,
                            $config->side,
                            $config->seed),
                    'mapped' => false
                ])
                ->add('attach', SubmitType::class)
                ->setMethod('PATCH')
        ;
    }

}
