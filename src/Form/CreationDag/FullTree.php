<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of FullTree
 *
 * @author trismegiste
 */
class FullTree extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('node', CollectionType::class, [
                    'entry_type' => NodeType::class,
                    'entry_options' => ['graph' => $options['data']['node']]
                ])
                ->add('save', SubmitType::class)
                ->setMethod('PUT')
        ;
    }

}
