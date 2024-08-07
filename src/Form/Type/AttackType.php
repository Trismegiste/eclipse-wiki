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

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('title', TextType::class)
                ->add('roll', SkillType::class, ['expanded' => false])
                ->add('rollBonus', IntegerType::class)
                ->add('rateOfFire', IntegerType::class)
                ->add('damage', DamageRollType::class)
                ->add('armorPiercing', IntegerType::class)
                ->add('reach', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Attack::class);
    }

}
