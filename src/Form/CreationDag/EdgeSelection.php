<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Form\Type\MultiCheckboxType;
use App\Repository\EdgeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A small selection of Edge
 */
class EdgeSelection extends AbstractType
{

    public function getParent(): string
    {
        return MultiCheckboxType::class;
    }

    public function __construct(protected EdgeProvider $provider)
    {
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $listing = $this->provider->getListing();
        $choices = [];
        foreach ($listing as $item) {
            if (!in_array($item->getCategory(), ['leg', 'etr']) && $item->isEgo() && in_array($item->getRank(), ['n', 'a'])) {
                $choices[$item->getCategory()][$item->getName() . ' (' . strtoupper($item->getRank()) . ')'] = $item->getName();
            }
        }
        $resolver->setDefaults([
            'choices' => $choices,
            'choice_translation_domain' => 'sawo'
        ]);
    }

}
