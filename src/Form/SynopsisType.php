<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Synopsis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A form for Synopsis entity
 */
class SynopsisType extends AbstractType
{

    public function getParent(): ?string
    {
        return VertexType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->remove('content')
                ->add('pitch', TextareaType::class)
                ->add('story', TextareaType::class)
                ->add('act', CollectionType::class, [
                    'entry_type' => TextareaType::class,
                    'entry_options' => [
                        'attr' => ['rows' => 3],
                        'required' => true,
                        'label' => false
                    ]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Synopsis($form->get('title')->getData());
        });
    }

}
