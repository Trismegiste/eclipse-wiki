<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Handout;
use App\Form\Type\WikitextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for Handout entity
 */
class HandoutType extends AbstractType
{

    public function getParent(): ?string
    {
        return VertexType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('content');
        $builder
                ->add('pcInfo', WikitextType::class, [
                    'required' => true,
                    'attr' => ['rows' => 16]
                ])
                ->add('gm_info', WikitextType::class, [
                    'required' => false,
                    'attr' => ['rows' => 16],
                    'property_path' => 'gmInfo'
                ])
                ->add('target', TextType::class, ['priority' => 2000]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Handout::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Handout($form->get('title')->getData());
        });
    }

}
