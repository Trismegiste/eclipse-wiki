<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of DagFocusNode
 *
 * @author trismegiste
 */
class DagFocusNode extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('focus');
        $resolver->setDefault('data_class', null);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['data'] as $idx => $node) {
            if ($node->name === $options['focus']) {
                break;
            }
        }

        $builder
                ->add('parents', ChoiceType::class, ['multiple' => true, 'expanded' => true, 'mapped' => false])
                ->add('node', NodeType::class, ['property_path' => "[$idx]"])
                ->add('save', SubmitType::class)
        ;
    }

}
