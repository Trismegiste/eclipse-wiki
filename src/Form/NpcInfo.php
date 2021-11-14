<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wikitext for the NPC
 */
class NpcInfo extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('information', TextareaType::class, ['attr' => ['rows' => 30]])
            ->add('edit', SubmitType::class)
            ->setMethod('PUT');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Character::class);
    }

}
