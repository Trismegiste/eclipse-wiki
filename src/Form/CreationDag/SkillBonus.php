<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Repository\SkillProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $listing = $this->provider->getListing();
        $choices = [];
        foreach ($listing as $skill) {
            $choices[$skill->getName()] = $skill->getName();
        }
        $resolver->setDefaults([
            'choices' => $choices,
            'multiple' => true,
            'expanded' => false,
            'attr' => ['size' => 6],
            'block_prefix' => 'multiselect_with_tags'
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new KeyBonusTransfo());
    }

}
