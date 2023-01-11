<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The tool box for running a battlemap
 */
class RunningMap3dGui extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('viewMode', ChoiceType::class, [
                    'choices'=> ['FPS' => 'fps', 'RTS'=>'rts', 'Perso'=>'populate'],
                    'attr' => ['x-on:change' => 'changeMode'],
                    'expanded' => true
                ])
                ->add('populateWith', \App\Form\Type\NpcChoiceType::class, [
                    'placeholder' => '---------',
                    'attr' => ['x-on:change' => 'changeNpc'],
                ])
        ;
    }

}
