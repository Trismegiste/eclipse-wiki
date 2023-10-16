<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Entity\CreationTree\Node;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form for a Node
 */
class NodeType extends AbstractType
{

    public function __construct(protected \App\Repository\BackgroundProvider $background, protected \App\Repository\FactionProvider $faction)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('name', TextType::class, ['constraints' => [new NotBlank()]])
                ->add('attributes', AttributeBonus::class)
                ->add('skills', SkillBonus::class, ['required' => false])
                ->add('edges', EdgeSelection::class, ['required' => false])
                ->add('networks', NetworkBonus::class, ['required' => false])
                ->add('backgrounds', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                    'choices' => $this->background->getListing(),
                    'multiple' => true,
                    'expanded' => false
                ])
                ->add('factions', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                    'choices' => $this->faction->getListing(),
                    'multiple' => true,
                    'expanded' => false
                ])
                ->add('children', NodeLinkType::class, [
                    'multiple' => true,
                    'expanded' => true,
                    'graph' => $options['graph']
                ])
        ;
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
