<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Stats of a NPC
 */
class NpcStats extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attributes', CollectionType::class, [
                'entry_type' => Type\AttributeType::class,
                'entry_options' => [
                    'expanded' => true,
                    'max_modif' => 4
                ]
            ])
            ->add('edit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Character::class);
    }

}
