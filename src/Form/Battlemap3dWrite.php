<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Place;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Write a battlemap
 */
class Battlemap3dWrite extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('battlemap3d', Type\Battlemap3dFile::class, ['unique_id' => $options['data']->getPk()])
                ->add('write', SubmitType::class)
                ->setMethod('PATCH')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('attr', ['x-on:submit' => "write"]);
        $resolver->setDefault('data_class', Place::class);
    }

}
