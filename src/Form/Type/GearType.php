<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Gear;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type for new Gear
 */
class GearType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Gear::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Gear($form->get('name')->getData());
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class);
    }

}
