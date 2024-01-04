<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Form\Type\MultiCheckboxType;
use App\Repository\SkillProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Bonus for Skills on a creation Node
 */
class SkillBonus extends AbstractType
{

    public function __construct(protected SkillProvider $provider)
    {
        
    }

    public function getParent(): string
    {
        return MultiCheckboxType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $listing = $this->provider->getListing();
        $choices = [];
        foreach ($listing as $skill) {
            $choices[$skill->getName()] = $skill->getName();
        }
        $resolver->setDefaults(['choices' => $choices]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new KeyBonusTransfo());
    }

}
