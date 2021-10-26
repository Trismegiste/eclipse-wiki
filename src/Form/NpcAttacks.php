<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use App\Form\Type\AttackType;
use App\Repository\MeleeWeaponProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of NpcAttacks
 */
class NpcAttacks extends AbstractType
{

    protected $melee;
    protected $ranged;

    public function __construct(MeleeWeaponProvider $m)
    {
        $this->melee = $m;
        //$this->ranged = $r;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('melee_weapon_list', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => $this->melee->getListing(),
                'choice_value' => function ($weap) {
                    return json_encode($weap);
                },
                'choice_label' => function ($weap) {
                    return "{$weap->name} : {$weap->damage} ({$weap->ap})";
                }
            ])
            ->add('attacks', CollectionType::class, [
                'entry_type' => AttackType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => false
            ])
            ->add('edit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Character::class);
    }

}
