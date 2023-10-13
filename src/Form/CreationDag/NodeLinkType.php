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
 * Description of NodeLinkType
 *
 * @author trismegiste
 */
class NodeLinkType extends AbstractType
{

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('graph');
        $resolver->setAllowedTypes('graph', 'array');
        $resolver->setDefault('choices', function (Options $options) {
            $choices = [];
            foreach ($options['graph'] as $node) {
                $choices[$node->name] = $node->name;
            }

            return $choices;
        });
    }

}
