<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Focus on one node from a DAG
 */
class DagFocusNode extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('focus');
        $resolver->setDefault('data_class', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $graph = $options['data'];
        $found = false;
        foreach ($graph as $idx => $node) {
            if ($node->name === $options['focus']) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw \InvalidArgumentException('focus is invalid');
        }

        $builder
                ->add('parents', NodeLinkType::class, [
                    'multiple' => true,
                    'expanded' => true,
                    'graph' => $graph,
                    'mapped' => false
                ])
                ->add('node', NodeType::class, ['property_path' => "[$idx]", 'graph' => $graph])
                ->add('save', SubmitType::class)
                ->setMethod('PUT')
        ;
        // mapper for parents
        $builder->get('parents')->setDataMapper(new ParentNodeMapper());
    }

}
