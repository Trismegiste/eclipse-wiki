<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Love letter form type
 */
class LoveLetter extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('player', TextType::class)
                ->add('context', TextareaType::class)
                ->add('drama', TextareaType::class)
                ->add('trait1', ChoiceType::class)
                ->add(('difficulty1'), NumberType::class)
                ->add('trait2', ChoiceType::class)
                ->add(('difficulty2'), NumberType::class)
                ->add('trait3', ChoiceType::class)
                ->add(('difficulty3'), NumberType::class)
                ->add('generate', SubmitType::class);
    }

}
