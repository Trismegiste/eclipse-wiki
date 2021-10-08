<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of ProviderChoiceType
 *
 * @author flo
 */
class ProviderChoiceType extends AbstractType
{

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('provider');
        $resolver->setDefault('placeholder', '-----');

        $resolver->setDefault('choices', function (Options $opt) {
            return $opt['provider']->getListing();
        });
    }

}
