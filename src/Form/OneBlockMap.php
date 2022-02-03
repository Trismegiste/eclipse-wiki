<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of OneBlockMap
 */
class OneBlockMap extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('seed', NumberType::class)
            ->add('side', NumberType::class)
            ->add('iteration', NumberType::class)
            ->add('npc', NumberType::class)
            ->add('create', SubmitType::class);
    }

}
