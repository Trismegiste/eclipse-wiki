<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Repository\AttributeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Bonus for Attribute on a creation Node
 */
class AttributeBonus extends AbstractType
{

    public function __construct(protected AttributeProvider $provider)
    {
        
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $listing = $this->provider->getListing();
        $choices = [];
        foreach ($listing as $attr) {
            $choices[$attr->getName()] = $attr->getName();
        }
        $resolver->setDefaults([
            'choices' => $choices,
            'multiple' => true,
            'expanded' => false,
            'attr' => ['size' => 5]
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new KeyBonusTransfo());
    }

}
