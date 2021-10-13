<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use App\Repository\TraitProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Stats of a NPC
 */
class NpcStats extends AbstractType
{

    protected $provider;

    public function __construct(TraitProvider $pro)
    {
        $this->provider = $pro;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('attributes', CollectionType::class, [
                    'entry_type' => Type\AttributeType::class,
                    'entry_options' => [
                        'expanded' => true,
                        'max_modif' => 0
                    ]
                ])
                ->add('skill_list', Type\TraitType::class, [
                    'mapped' => false,
                    'category' => 'skill',
                    'expanded' => true,
                    'multiple' => true
                ])
                ->add('skills', CollectionType::class, [
                    'entry_type' => Type\SkillType::class,
                    'entry_options' => [
                        'expanded' => true,
                        'max_modif' => 0
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype_data' => $this->getProtoData()
                ])
                ->add('edit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Character::class);
    }

    protected function getProtoData()
    {
        $obj = new \App\Entity\Skill('__undefined__', '__ATTR__');
        $obj->dice = 4;

        return $obj;
    }

}
