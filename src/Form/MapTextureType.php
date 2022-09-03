<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\MapConfig;
use App\Repository\TileProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', MapConfig::class);
        $resolver->setRequired('tileset');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->provider->getClusterSet($options['tileset']) as $tile) {
            $builder->add($tile->getKey(), IntegerType::class, ['mapped' => false, 'required' => false]);
        }
        $builder->add('texture', SubmitType::class);
        $builder->setMethod('PUT');
    }

}
