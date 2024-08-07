<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Entity\CreationTree\Node;
use App\Form\Type\MultiCheckboxType;
use App\Repository\BackgroundProvider;
use App\Repository\FactionProvider;
use App\Repository\MorphProvider;
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

    public function __construct(protected BackgroundProvider $background, protected FactionProvider $faction, protected MorphProvider $morph)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['mode'] === 'creation') {
            $builder->add('name', TextType::class, ['constraints' => [new NotBlank()]]);
        }

        $builder
                ->add('attributes', AttributeBonus::class, ['required' => false])
                ->add('skills', SkillBonus::class, ['required' => false])
                ->add('edges', EdgeSelection::class, ['required' => false])
                ->add('networks', NetworkBonus::class, ['required' => false])
                ->add('backgrounds', MultiCheckboxType::class, [
                    'choices' => $this->background->getListing()
                ])
                ->add('factions', MultiCheckboxType::class, [
                    'choices' => $this->faction->getListing()
                ])
                ->add('morphs', MultiCheckboxType::class, [
                    'choices' => $this->morph->getListing()
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
        $resolver->setDefault('mode', 'edition');
        $resolver->setAllowedValues('mode', ['edition', 'creation']);
    }

}
