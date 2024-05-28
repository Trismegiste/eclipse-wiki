<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Timeline;
use App\Form\Type\TreeBuilderMapper;
use App\Form\Type\WikitextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Creation of timeline
 */
class TimelineCreate extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('content');
        $builder->add('elevatorPitch', WikitextType::class, ['attr' => ['rows' => 4]])
                ->add('tree', CollectionType::class, [
                    'entry_type' => WikitextType::class,
                    'entry_options' => [
                        'attr' => ['rows' => 3],
                        'required' => false,
                        'label' => false
                    ],
                    'delete_empty' => true,
                    'data' => array_fill(0, 5, null),
                    'label' => '5 acts'
                ])
        ;
        $builder->get('tree')->setDataMapper(new TreeBuilderMapper());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Timeline($form->get('title')->getData());
        });
    }

    public function getParent(): ?string
    {
        return VertexType::class;
    }

}
