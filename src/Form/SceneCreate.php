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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Twig\Environment;

/**
 * It's a template for creating a scene
 */
class SceneCreate extends AbstractType
{

    use FormTypeUtils;

    protected VertexRepository $repository;
    protected Environment $twig;

    public function __construct(VertexRepository $repo, Environment $twig)
    {
        $this->repository = $repo;
        $this->twig = $twig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('content');
        $builder->add('place', Type\AutocompleteType::class, [
                    'choices' => $this->getPlaceTitle(),
                    'required' => false
                ])
                ->add('ambience', Type\WikitextType::class, [
                    'attr' => ['rows' => 4],
                    'required' => false
                ])
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
                ->add('prerequisite', Type\WikitextType::class, ['required' => false, 'attr' => ['rows' => 4]])
                ->add('event', Type\WikitextType::class, ['attr' => ['rows' => 4]])
                ->add('outcome', Type\WikitextType::class, ['attr' => ['rows' => 4]])
                ->add('append_timeline', Type\AutocompleteType::class, ['required' => false, 'choices' => $this->getTimelineTitle()])
        ;
        $builder->setDataMapper(new Type\WikitextContentMapper($this->twig, 'scene/content.wiki.twig'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'empty_data' => function (FormInterface $form) {
                $title = $form->get('title')->getData();
                return (!is_null($title)) ? new Scene($title) : null;
            },
            'constraints' => new Callback(function (Scene $scene, ExecutionContextInterface $ctx) {
                        $place = $ctx->getRoot()['place']->getData();
                        $ambience = $ctx->getRoot()['ambience']->getData();
                        if (empty($place) && empty($ambience)) {
                            $ctx->buildViolation('Either Place or Ambience should be filled')
                                    ->atPath('place')
                                    ->addViolation();
                        }
                    })
        ]);
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