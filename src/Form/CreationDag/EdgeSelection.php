<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Repository\EdgeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A small selection of Edge
 */
class EdgeSelection extends AbstractType
{

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function __construct(protected EdgeProvider $provider)
    {
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $listing = $this->provider->getListing();
        $choices = [];
        foreach ($listing as $item) {
            if (!in_array($item->getCategory(), ['leg', 'etr']) && in_array($item->getRank(), ['n', 'a'])) {
                $choices[$item->getCategory()][$item->getName() . ' (' . strtoupper($item->getRank()) . ')'] = $item->getName();
            }
        }
        $resolver->setDefaults([
            'choices' => $choices,
            'multiple' => true,
            'expanded' => false,
            'attr' => ['size' => 6],
            'block_prefix' => 'multiselect_with_tags',
            'choice_translation_domain' => 'sawo'
        ]);
    }

}
