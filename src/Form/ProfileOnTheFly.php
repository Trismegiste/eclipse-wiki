<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for generating a quick npc profile
 */
class ProfileOnTheFly extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('svg', TextType::class)
                ->add('name', TextType::class)
                ->add('template', TextType::class)
                ->add('generate', SubmitType::class)
        ;
    }

}
