<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Repository\TraitProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Bonus for Social Networks
 */
class NetworkBonus extends AbstractType
{

    public function __construct(protected TraitProvider $provider)
    {
        
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $listing = $this->provider->findSocialNetworks();
        $resolver->setDefaults([
            'choices' => $listing,
            'multiple' => true,
            'expanded' => true
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new KeyBonusTransfo());
    }

}
