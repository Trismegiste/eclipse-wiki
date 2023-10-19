<?php

/*
 * eclipse-wiki
 */

namespace App\Form\QuickNpc;

use App\Entity\Edge;
use App\Repository\EdgeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Selection of Edge for creation
 */
class EdgeCheckType extends AbstractType
{

    public function __construct(protected EdgeProvider $provider)
    {
        
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = $this->provider->getListing();
        array_walk($choices, function (Edge $e) {
            $e->origin = 'Progression';
        });

        $resolver
                ->setDefault('multiple', true)
                ->setDefault('expanded', true)
                ->setDefault('choices', $choices)
                ->setDefault('choice_value', function (Edge $e) {
                    return $e->getName();
                })
        ;
    }

}
