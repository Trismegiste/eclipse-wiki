<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Entity\CreationTree\Node;
use App\Repository\BackgroundProvider;
use App\Repository\FactionProvider;
use App\Repository\MorphProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for a Node
 */
class NodeType extends AbstractType
{

    public function __construct(protected BackgroundProvider $background, protected FactionProvider $faction, protected MorphProvider $morph)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('attributes', AttributeBonus::class, ['required' => false])
                ->add('skills', SkillBonus::class, ['required' => false])
                ->add('edges', EdgeSelection::class, ['required' => false])
                ->add('networks', NetworkBonus::class, ['required' => false])
                ->add('backgrounds', ChoiceType::class, [
                    'choices' => $this->background->getListing(),
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                    'attr' => ['size' => 6],
                    'block_prefix' => 'multiselect_with_tags'
                ])
                ->add('factions', ChoiceType::class, [
                    'choices' => $this->faction->getListing(),
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                    'attr' => ['size' => 6],
                    'block_prefix' => 'multiselect_with_tags'
                ])
                ->add('morphs', ChoiceType::class, [
                    'choices' => $this->morph->getListing(),
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                    'attr' => ['size' => 6],
                    'block_prefix' => 'multiselect_with_tags'
                ])
                ->add('text2img', TextType::class, ['required' => false])
                ->add('children', NodeLinkType::class, [
                    'multiple' => true,
                    'expanded' => true,
                    'graph' => $options['graph']
                ])
        ;

        $builder->get('text2img')->addModelTransformer(new KeywordSplitter());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Node::class);
        $resolver->setRequired('graph');
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Node($form['name']->getData());
        });
    }

}
