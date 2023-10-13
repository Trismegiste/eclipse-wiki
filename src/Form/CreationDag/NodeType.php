<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Entity\CreationTree\Node;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of NodeType
 *
 * @author trismegiste
 */
class NodeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('name', TextType::class)
                ->add('children', NodeLinkType::class, ['multiple' => true, 'expanded' => false, 'graph' => $options['graph']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Node::class);
        $resolver->setRequired('graph');
    }

}
