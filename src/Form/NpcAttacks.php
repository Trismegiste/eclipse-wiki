<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Attack;
use App\Entity\Character;
use App\Entity\Skill;
use App\Form\Type\AttackType;
use App\Repository\ArmorProvider;
use App\Repository\MeleeWeaponProvider;
use App\Repository\RangedWeaponProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
    protected $armor;

    public function __construct(MeleeWeaponProvider $m, RangedWeaponProvider $r, ArmorProvider $armor)
    {
        $this->melee = $m;
        $this->ranged = $r;
        $this->armor = $armor;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parryBonus', IntegerType::class, ['attr' => ['class' => 'pure-input-4']])
            ->add('rangedMalus', IntegerType::class, ['attr' => ['class' => 'pure-input-4']])
            ->add('toughnessBonus', IntegerType::class, ['attr' => ['class' => 'pure-input-4']])
            ->add('melee_weapon_list', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => $this->getMelee(),
                'choice_value' => function ($weap) {
                    return json_encode($weap);
                },
                'choice_label' => function ($weap) {
                    return "{$weap->name} : {$weap->damage} (PA {$weap->ap}) minFOR=d{$weap->minStr}";
                },
                'attr' => ['x-on:change' => 'addMeleeWeapon']
            ])
            ->add('ranged_weapon_list', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => $this->getRanged(),
                'choice_value' => function ($weap) {
                    return json_encode($weap);
                },
                'choice_label' => function ($weap) {
                    return "{$weap->name} : CdT×{$weap->rof} {$weap->damage} (PA {$weap->ap}) minFOR=d{$weap->minStr}";
                },
                'attr' => ['x-on:change' => 'addRangedWeapon']
            ])
            ->add('attacks', CollectionType::class, [
                'entry_type' => AttackType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_data' => $this->createProtoAttackData()
            ])
            ->add('armor_list', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'choices' => $this->getArmor(),
                'choice_value' => function ($arm) {
                    return json_encode($arm);
                },
                'choice_label' => function ($arm) {
                    return "{$arm->name} : {$arm->protect}+{$arm->special} (zone: {$arm->zone})";
                },
                'attr' => ['x-on:change' => 'addArmor']
            ])
            ->add('armors', CollectionType::class, [
                'entry_type' => Type\ArmorType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_data' => $this->createProtoArmorData()
            ])
            ->setMethod('PUT')
            ->add('edit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Character::class);
    }

    protected function createProtoAttackData()
    {
        $attack = new Attack();
        $attack->roll = new Skill('yolo', 'DUM');

        return $attack;
    }

    protected function createProtoArmorData()
    {
        return new \App\Entity\Armor();
    }

    protected function getMelee(): array
    {
        $listing = $this->melee->getListing();
        $generic = new \App\Entity\MeleeWeapon('Attaque contact générique', 'FOR+d4', 0);
        array_unshift($listing, $generic);

        return $listing;
    }

    protected function getRanged(): array
    {
        $listing = $this->ranged->getListing();
        $generic = new \App\Entity\RangedWeapon('Attaque distance générique', '2d6', 0, 1, '12/24/48');
        array_unshift($listing, $generic);

        return $listing;
    }

    protected function getArmor(): array
    {
        $listing = $this->armor->getListing();
        $generic = new \App\Entity\Armor('Morphe', 4, '', 'T/B/J/H');
        array_unshift($listing, $generic);

        return $listing;
    }

}
