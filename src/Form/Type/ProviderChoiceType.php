<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Repository\GenericProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A ChoiceType using a GenericProvider
 */
class ProviderChoiceType extends AbstractType
{

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new ProviderTransformer($options['provider']));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('provider');
        $resolver->setDefault('placeholder', '-----');

        $resolver->setDefault('choices', function (Options $opt) {
            if (!$opt['provider'] instanceof GenericProvider) {
                throw new InvalidConfigurationException();
            }

            return $opt['provider']->getListing();
        });
    }

}
