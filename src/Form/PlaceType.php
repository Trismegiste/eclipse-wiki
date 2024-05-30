<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Place;
use App\Form\Type\FullTextChoice;
use App\Form\Type\RandomNameType;
use App\Form\Type\YoutubeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for creating and editing a Place
 */
class PlaceType extends AbstractType
{

    use FormTypeUtils;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['method'] !== 'PUT') {
            $builder->add('title', RandomNameType::class, ['priority' => 1000]);
        }

        $builder
                ->add('world', FullTextChoice::class, ['category' => 'world', 'priority' => 500])
                ->add('gravity', FullTextChoice::class, ['category' => 'gravity', 'priority' => 500])
                ->add('temperature', FullTextChoice::class, ['category' => 'temperature', 'priority' => 500])
                ->add('pressure', FullTextChoice::class, ['category' => 'pressure', 'priority' => 500])
                ->add('youtubeUrl', YoutubeType::class, [
                    'required' => false,
                    'label' => 'Youtube ID',
                    'attr' => [
                        'class' => 'pure-input-1-2',
                        'placeholder' => 'ID unique de Youtube ou url de la vidéo'
                    ],
                    'priority' => 500
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
            'empty_data' => function (FormInterface $form) {
                return new Place($form->get('title')->getData());
            }
        ]);
    }

    public function getParent(): ?string
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $this->changeAttribute($view, 'content', 'rows', 24);
    }

}
