<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Vertex;
use App\Form\Type\WikitextType;
use App\Form\Type\WikiTitleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Type for Vertex
 */
class VertexType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['edit']) {
            $builder
                    ->add('title', WikiTitleType::class)
                    ->add('content', WikitextType::class, ['attr' => ['rows' => 30, 'data-autofocus' => null]])
                    ->add('create', SubmitType::class);
        } else {
            $builder
                    ->add('content', WikitextType::class, ['attr' => ['rows' => 30, 'data-autofocus' => null]])
                    ->add('create', SubmitType::class, ['label' => 'Edit'])
                    ->setMethod('PUT');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'edit' => false,
            'data_class' => Vertex::class
        ]);
    }

}
