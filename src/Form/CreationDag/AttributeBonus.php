<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Repository\AttributeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Bonus for Attribute on a creation Node
 */
class AttributeBonus extends AbstractType implements DataTransformerInterface
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
            $choices[$attr->getAbbrev()] = $attr->getAbbrev();
        }
        $resolver->setDefaults([
            'choices' => $choices,
            'multiple' => true,
            'expanded' => true
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this);
    }

    public function reverseTransform(mixed $value): mixed
    {
        $bonusModel = [];
        foreach ($value as $attr) {
            $bonusModel[$attr] = 1;
        }

        return $bonusModel;
    }

    public function transform(mixed $value): mixed
    {
        $choiceView = [];
        foreach ($value as $attr => $bonus) {
            $choiceView[$attr] = $attr;
        }

        return $choiceView;
    }

}
