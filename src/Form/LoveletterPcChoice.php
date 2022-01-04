<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Loveletter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PC choice(s) for different resolutions from a love letter
 */
class LoveletterPcChoice extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Loveletter::class);
        $resolver->setDefined('edit');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('pc_choice', ChoiceType::class, [
                    'expanded' => true,
                    'multiple' => true,
                    'choices' => range(0, 4),  // dummy label replaced in the twig by resolution
                    'property_path' => 'pcChoice'
                ])
                ->add('select', SubmitType::class)
                ->setMethod('PUT');
    }

}
