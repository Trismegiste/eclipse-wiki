<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Choicetype of links to nodes from a DAG
 */
class NodeLinkType extends AbstractType
{

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('required', false);
        $resolver->setRequired('graph');
        $resolver->setAllowedTypes('graph', 'array');
        $resolver->setDefault('choices', function (Options $options) {
            $choices = [];
            foreach ($options['graph'] as $node) {
                $choices[$node->name] = $node->name;
            }

            return $choices;
        });
        $resolver->setDefault('row_attr', ['class' => 'pure-u-1']);
    }

}
