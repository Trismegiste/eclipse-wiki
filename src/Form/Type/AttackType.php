<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\Attack;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for an Attack
 */
class AttackType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('title', TextType::class)
                ->add('roll', SkillType::class, ['expanded' => true])
                ->add('rollBonus', IntegerType::class)
                ->add('rateOfFire', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Attack::class);
    }

}
