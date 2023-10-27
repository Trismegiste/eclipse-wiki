<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Entity\CreationTree\Graph;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Edit the creation graph
 */
class FullTree extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('node', CollectionType::class, [
                    'entry_type' => NodeType::class,
                    'entry_options' => ['graph' => $options['data']->node],
                    'allow_add' => true,
                    'prototype_options' => ['mode' => 'creation']
                ])
                ->add('save', SubmitType::class)
                ->setMethod('PUT')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Graph::class);
    }

}
