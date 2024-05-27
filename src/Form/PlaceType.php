<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Place;
use App\Repository\VertexRepository;
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

    protected $repository;

    public function __construct(VertexRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['method'] !== 'PUT') {
            $builder->add('title', Type\RandomNameType::class);
        }

        $builder
                ->add('world', Type\FullTextChoice::class, ['category' => 'world'])
                ->add('gravity', Type\FullTextChoice::class, ['category' => 'gravity'])
                ->add('temperature', Type\FullTextChoice::class, ['category' => 'temperature'])
                ->add('pressure', Type\FullTextChoice::class, ['category' => 'pressure'])
                ->add('youtubeUrl', Type\YoutubeType::class, [
                    'required' => false,
                    'label' => 'Youtube ID',
                    'attr' => [
                        'class' => 'pure-input-1-2',
                        'placeholder' => 'ID unique de Youtube ou url de la vidéo'
                    ]
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
        $this->moveChildAtEnd($view, 'content');
        $this->moveChildAtEnd($view, 'create');
    }

}
