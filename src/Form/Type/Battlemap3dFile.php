<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Service\Storage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Battlemap3d file content
 */
class Battlemap3dFile extends AbstractType
{

    protected Storage $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function getParent()
    {
        return HiddenType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('unique_id');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new Battlemap3dTransfo($this->storage, $options['unique_id']));
    }

}
