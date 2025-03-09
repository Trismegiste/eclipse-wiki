<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use App\Entity\Edge;
use App\Entity\Hindrance;
use App\Entity\Skill;
use App\Form\Type\AttributeType;
use App\Form\Type\EconomyType;
use App\Form\Type\EdgeType;
use App\Form\Type\HindranceType;
use App\Form\Type\SkillType;
use App\Form\Type\TraitType;
use App\Repository\EdgeProvider;
use App\Repository\HindranceProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Stats of a NPC
 */
class NpcStats extends AbstractType
{

    protected $provider;
    protected $edge;
    protected $hindrance;

    public function __construct(EdgeProvider $edge, HindranceProvider $hindrance, protected TranslatorInterface $translator)
    {
        $this->edge = $edge;
        $this->hindrance = $hindrance;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('attributes', CollectionType::class, [
                    'entry_type' => AttributeType::class,
                    'entry_options' => [
                        'expanded' => true,
                        'max_modif' => 2
                    ]
                ])
                ->add('skill_list', TraitType::class, [
                    'mapped' => false,
                    'category' => 'skill',
                    'expanded' => true,
                    'multiple' => true,
                    'attr' => ['x-on:change' => 'checkingSkill($event.target)'],
                    'choice_attr' => function (string $idx) {
                        return ['x-bind:checked' => "hasSkill('$idx')"];
                    }
                ])
                ->add('skills', CollectionType::class, [
                    'entry_type' => SkillType::class,
                    'entry_options' => [
                        'expanded' => true,
                        'max_modif' => 2
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype_data' => $this->getProtoSkill(),
                    'by_reference' => false
                ])
                ->add('edge_filter', ChoiceType::class, [
                    'mapped' => false,
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => $this->getFilterEdge(),
                    'choice_attr' => function ($choice, $key, $value) {
                        return ['x-model' => 'edgeFilter.' . $value];
                    }
                ])
                ->add('edge_list', ChoiceType::class, [
                    'placeholder' => '-------------',
                    'mapped' => false,
                    'required' => false,
                    'choices' => $this->edge->getListing(),
                    'choice_value' => function (?Edge $edge) {
                        return json_encode($edge);
                    },
                    'choice_label' => [$this, 'printEdge'],
                    'attr' => [
                        'x-on:change' => 'edges.push(JSON.parse($event.target.value)); $el.value=""'
                    ],
                    'choice_attr' => function (?Edge $edge) {
                        return [
                    'data-key' => $edge->getName(),
                    'x-show' => 'edgeFilter.' . $edge->getCategory()
                        ];
                    }
                ])
                ->add('edges', CollectionType::class, [
                    'entry_type' => EdgeType::class,
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
                    'choice_label' => [$this, 'printHindrance'],
                    'attr' => ['x-on:change' => 'hindrances.push(JSON.parse($event.target.value)); $el.value=""']
                ])
                ->add('hindrances', CollectionType::class, [
                    'entry_type' => HindranceType::class,
                    'allow_add' => true,
                    'allow_delete' => true
                ])
                ->add('newEconomy', CollectionType::class, [
                    'entry_type' => IntegerType::class,
                    'entry_options' => [
                        'attr' => ['min' => 0, "max" => 10, 'class' => 'pure-u-1-4'],
                        'required' => false
                    ],
                    'allow_add' => true
                ])
                ->add('economy', EconomyType::class)
                ->add('edit', SubmitType::class)
                ->setMethod('PUT');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Character::class);
    }

    protected function getProtoSkill()
    {
        $obj = new Skill('__undefined__', '__ATTR__');
        $obj->dice = 4;

        return $obj;
    }

    public function printHindrance(Hindrance $hind): string
    {
        $str = $hind->getName() . ' (' . HindranceProvider::paramType[$hind->getChoices()] . ')';
        $type = [];
        if ($hind->isEgo()) {
            $type[] = 'Ego';
        }
        if ($hind->isBio()) {
            $type[] = 'Bio';
        }
        if ($hind->isSynth()) {
            $type[] = 'Synth';
        }
        $str .= ' : ' . implode('/', $type);

        return $str;
    }

    public function printEdge(Edge $edge): string
    {
        $type = [];
        if ($edge->isEgo()) {
            $type[] = 'Ego';
        }
        if ($edge->isBio()) {
            $type[] = 'Bio';
        }
        if ($edge->isSynth()) {
            $type[] = 'Synth';
        }

        return $edge->getName()
                . ' (' . strtoupper($edge->getRank()) . ') : ['
                . implode('/', $type) . '] '
                . str_replace(['[[', ']]'], '', $edge->getPrerequisite());
    }

    private function getFilterEdge(): array
    {
        $categ = [];
        foreach ($this->edge->getAllEdgeCategory() as $entry) {
            $categ [$this->translator->trans($entry, domain: 'sawo')] = $entry;
        }

        return $categ;
    }
}
