<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Tool3d;

use App\Entity\Place;
use App\Form\Type\Battlemap3dFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Write a battlemap
 */
class Battlemap3dWrite extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('battlemap3d', Battlemap3dFile::class, ['unique_id' => $options['data']->getPk()])
                ->add('write', SubmitType::class)
                ->setMethod('PATCH')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('attr', ['x-on:submit.prevent' => "write"]);
        $resolver->setDefault('csrf_protection', false);
        $resolver->setDefault('data_class', Place::class);
    }

}
