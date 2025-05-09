<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\SaWoTrait;
use App\Repository\GenericProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Generic type for SaWoTrait
 */
class SaWoTraitType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('name', HiddenType::class)
                ->add('roll', ChoiceType::class, [
                    'choices' => $this->getChoices($options['max_modif']),
                    'expanded' => $options['expanded']
                ])
                ->setDataMapper(new SaWoTraitMapper($options['provider']))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('provider');
        $resolver->setAllowedTypes('provider', GenericProvider::class);

        $resolver->setDefaults([
            'data_class' => SaWoTrait::class,
            'expanded' => false,
            'max_modif' => 2,
            'empty_data' => function  (FormInterface $form) {
                    /** @var GenericProvider $provider */
                    $provider = $form->getConfig()->getOption('provider');
                    return $provider->findOne($form->get('name')->getData());
            }
        ]);
    }

    protected function getChoices(int $max): array
    {
        $choices = [];
        for ($k = 4; $k <= 12; $k += 2) {
            $choices["d$k"] = $k;
        }
        for ($k = 1; $k <= $max; $k++) {
            $choices["d12+$k"] = 12 + $k;
        }

        return $choices;
    }

}
