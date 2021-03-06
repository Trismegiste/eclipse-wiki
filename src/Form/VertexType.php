<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Vertex;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type for Vertex
 */
class VertexType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['edit']) {
            $builder
                    ->add('title', TextType::class, ['constraints' => [new \App\Validator\UniqueVertexTitle()]])
                    ->add('content', TextareaType::class, ['attr' => ['rows' => 30]])
                    ->add('create', SubmitType::class);
        } else {
            $builder
                    ->add('content', TextareaType::class, ['attr' => ['rows' => 30]])
                    ->add('create', SubmitType::class, ['label' => 'Edit'])
                    ->setMethod('PUT');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'edit' => false,
            'data_class' => Vertex::class,
            'empty_data' => function (FormInterface $form) {
                return new Vertex($form->get('title')->getData());
            }
        ]);
    }

}
