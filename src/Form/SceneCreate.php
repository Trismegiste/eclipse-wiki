<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Ali;
use App\Entity\Freeform;
use App\Entity\Place;
use App\Entity\Scene;
use App\Entity\Timeline;
use App\Entity\Transhuman;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * It's a template for creating a scene
 */
class SceneCreate extends AbstractType
{

    use FormTypeUtils;

    protected VertexRepository $repository;

    public function __construct(VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('content');
        $builder->add('place', Type\AutocompleteType::class, ['choices' => $this->getPlaceTitle()])
                ->add('ambience', TextareaType::class, ['attr' => ['rows' => 4]])
                ->add('npc', CollectionType::class, [
                    'entry_type' => Type\AutocompleteType::class,
                    'entry_options' => [
                        'choices' => $this->getNpcTitle(),
                        'required' => false,
                        'label' => false
                    ],
                    'delete_empty' => true,
                    'data' => array_fill(0, 4, null)
                ])
                ->add('prerequisite', TextareaType::class, ['required' => false, 'attr' => ['rows' => 4]])
                ->add('event', TextareaType::class, ['attr' => ['rows' => 4]])
                ->add('outcome', TextareaType::class, ['attr' => ['rows' => 4]])
                ->add('append_timeline', Type\AutocompleteType::class, ['required' => false, 'choices' => $this->getTimelineTitle()])
        ;
        $builder->setDataMapper(new Type\WikitextContentMapper());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Scene($form->get('title')->getData());
        });
    }

    public function getParent(): ?string
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->moveChildAtEnd($view, 'create');
    }

    protected function getPlaceTitle(): iterable
    {
        $choice = [];
        foreach ($this->repository->findByClass(Place::class) as $vertex) {
            $choice[] = $vertex->getTitle();
        }

        return $choice;
    }

    protected function getTimelineTitle(): iterable
    {
        $choice = [];
        foreach ($this->repository->findByClass(Timeline::class) as $vertex) {
            $choice[] = $vertex->getTitle();
        }

        return $choice;
    }

    protected function getNpcTitle(): iterable
    {
        $choice = [];
        foreach ($this->repository->findByClass([
            Ali::class,
            Transhuman::class,
            Freeform::class
        ]) as $vertex) {
            $choice[] = $vertex->getTitle();
        }

        return $choice;
    }

}
