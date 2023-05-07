<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Vertex;
use App\Validator\UniqueVertexTitle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Type for Vertex
 */
class VertexType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['edit']) {
            $builder
                    ->add('title', TextType::class, [
                        'constraints' => [
                            new Regex(Vertex::FORBIDDEN_REGEX_TITLE, match: false),
                            new NotBlank(),
                            new UniqueVertexTitle()
                        ]
                    ])
                    ->add('content', Type\WikitextType::class, ['attr' => ['rows' => 30]])
                    ->add('create', SubmitType::class);
        } else {
            $builder
                    ->add('content', Type\WikitextType::class, ['attr' => ['rows' => 30]])
                    ->add('create', SubmitType::class, ['label' => 'Edit'])
                    ->setMethod('PUT');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'edit' => false,
            'data_class' => Vertex::class
        ]);
    }

}
