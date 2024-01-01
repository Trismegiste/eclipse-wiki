<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for resynchronize an NPC with its template NPC
 */
class NpcResync extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data', Character::class);
        $resolver->setRequired('template');
        $resolver->setAllowedTypes('template', Character::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('attributes', CheckboxType::class, ['required' => false, 'mapped' => false])
                ->add('skills', CheckboxType::class, ['required' => false, 'mapped' => false])
                ->add('edges', CheckboxType::class, ['required' => false, 'mapped' => false])
                ->add('economy', CheckboxType::class, ['required' => false, 'mapped' => false])
                ->add('attacks', CheckboxType::class, ['required' => false, 'mapped' => false])
                ->add('armors', CheckboxType::class, ['required' => false, 'mapped' => false])
                ->add('synchronize', SubmitType::class)
                ->setMethod('PUT')
                ->setDataMapper(new Type\ResyncMapper($options['template']))
        ;
    }

}
