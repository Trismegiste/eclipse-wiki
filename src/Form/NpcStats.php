<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use App\Entity\Skill;
use App\Repository\EdgeProvider;
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

    public function __construct(TraitProvider $pro, EdgeProvider $edge)
    {
        $this->provider = $pro;
        $this->edge = $edge;
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
                'prototype_data' => $this->getProtoData(),
                'by_reference' => false
            ])
            ->add('edge_category_list', ChoiceType::class, [
                'mapped' => false,
                'choices' => $this->edge->getListing(),
                'group_by' => function (\App\Entity\Edge $edge) {
                    return $edge->getCategory();
                },
                'choice_filter' => 'isEgo',
                'choice_value' => 'name',
                'choice_label' => function(?\App\Entity\Edge $edge) {
                    return $edge ? ($edge->getName()
                        . ' (' . strtoupper($edge->getRank()) . ') : '
                        . str_replace(['[[', ']]'], '', $edge->getPrerequisite())) : '';
                }
            ])
            //->add('edges', ChoiceType::class, ['choices' => $this->edge->getListing(), 'expanded' => true, 'multiple' => true])
            ->add('edit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Character::class);
    }

    protected function getProtoData()
    {
        $obj = new Skill('__undefined__', '__ATTR__');
        $obj->dice = 4;

        return $obj;
    }

}
