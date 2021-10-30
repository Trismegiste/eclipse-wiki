<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Attack;
use App\Entity\Character;
use App\Entity\Skill;
use App\Form\Type\AttackType;
use App\Repository\MeleeWeaponProvider;
use App\Repository\RangedWeaponProvider;
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

    public function __construct(MeleeWeaponProvider $m, RangedWeaponProvider $r)
    {
        $this->melee = $m;
        $this->ranged = $r;
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
                    return "{$weap->name} : {$weap->damage} (PA {$weap->ap})";
                },
                'attr' => ['x-on:change' => 'addMeleeWeapon']
            ])
            ->add('ranged_weapon_list', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => $this->ranged->getListing(),
                'choice_value' => function ($weap) {
                    return json_encode($weap);
                },
                'choice_label' => function ($weap) {
                    return "{$weap->name} : CdT×{$weap->rof} {$weap->damage} (PA {$weap->ap})";
                },
                'attr' => ['x-on:change' => 'addRangedWeapon']
            ])
            ->add('attacks', CollectionType::class, [
                'entry_type' => AttackType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_data' => $this->createPrototypeData()
            ])
            ->setMethod('PUT')
            ->add('edit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Character::class);
    }

    protected function createPrototypeData()
    {
        $attack = new Attack();
        $attack->roll = new Skill('yolo', 'ZOB');

        return $attack;
    }

}
