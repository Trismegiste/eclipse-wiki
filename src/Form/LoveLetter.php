<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
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
        $builder
            ->add('player', TextType::class)
            ->add('context', TextareaType::class)
            ->add('drama', TextareaType::class)
            ->add('roll1', Type\RollType::class)
            ->add('roll2', Type\RollType::class)
            ->add('roll3', Type\RollType::class)
            ->add('choice1', TextareaType::class, ['required' => true])
            ->add('choice2', TextareaType::class, ['required' => true])
            ->add('choice3', TextareaType::class, ['required' => true])
            ->add('choice4', TextareaType::class, ['required' => true])
            ->add('choice5', TextareaType::class, ['required' => false])
            ->add('generate', SubmitType::class);
    }

}
