<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use App\Entity\Edge;
use App\Entity\Hindrance;
use App\Entity\Skill;
use App\Repository\EdgeProvider;
use App\Repository\HindranceProvider;
use App\Repository\TraitProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
    protected $edge;
    protected $hindrance;

    public function __construct(TraitProvider $pro, EdgeProvider $edge, HindranceProvider $hindrance)
    {
        $this->provider = $pro;
        $this->edge = $edge;
        $this->hindrance = $hindrance;
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
                    'prototype_data' => $this->getProtoSkill(),
                    'by_reference' => false
                ])
                ->add('edge_list', ChoiceType::class, [
                    'placeholder' => '-------------',
                    'mapped' => false,
                    'required' => false,
                    'choices' => $this->edge->getListing(),
                    'group_by' => function (Edge $edge) {
                        return $edge->getCategory();
                    },
                    'choice_value' => function (?Edge $edge) {
                        return json_encode($edge);
                    },
                    'choice_label' => function (?Edge $edge) {
                        return $edge ? ($edge->getName()
                        . ' (' . strtoupper($edge->getRank()) . ') : '
                        . str_replace(['[[', ']]'], '', $edge->getPrerequisite())) : '';
                    },
                    'attr' => ['x-on:change' => 'edges.push(JSON.parse($event.target.value)); $el.value=""']
                ])
                ->add('edges', CollectionType::class, [
                    'entry_type' => Type\EdgeType::class,
                    'allow_add' => true,
                    'allow_delete' => true
                ])
                ->add('hindrance_list', ChoiceType::class, [
                    'placeholder' => '-------------',
                    'mapped' => false,
                    'required' => false,
                    'choices' => $this->hindrance->getListing(),
                    'choice_value' => function (?Hindrance $hind) {
                        return json_encode($hind);
                    },
                    'choice_label' => function (?Hindrance $hind) {
                        return $hind->getName() . ' (' . HindranceProvider::paramType[$hind->getChoices()] . ')';
                    },
                    'attr' => ['x-on:change' => 'hindrances.push(JSON.parse($event.target.value)); $el.value=""']
                ])
                ->add('hindrances', CollectionType::class, [
                    'entry_type' => Type\HindranceType::class,
                    'allow_add' => true,
                    'allow_delete' => true
                ])
                ->add('edit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Character::class);
    }

    protected function getProtoSkill()
    {
        $obj = new Skill('__undefined__', '__ATTR__');
        $obj->dice = 4;

        return $obj;
    }

}
