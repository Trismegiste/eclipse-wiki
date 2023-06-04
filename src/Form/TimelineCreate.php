<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Timeline;
use App\Form\Type\WikitextContentMapper;
use App\Form\Type\WikitextType;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Creation of timeline
 */
class TimelineCreate extends AbstractType
{

    use FormTypeUtils;

    protected VertexRepository $repository;
    protected Environment $twig;

    public function __construct(VertexRepository $repo, Environment $twig)
    {
        $this->repository = $repo;
        $this->twig = $twig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('content');
        $builder->add('elevator_pitch', WikitextType::class, ['attr' => ['rows' => 4]])
                ->add('scene', CollectionType::class, [
                    'entry_type' => WikitextType::class,
                    'entry_options' => [
                        'attr' => ['rows' => 2],
                        'required' => false,
                        'label' => false
                    ],
                    'delete_empty' => true,
                    'data' => array_fill(0, 5, null)
                ])
        ;
        $builder->setDataMapper(new WikitextContentMapper($this->twig, 'timeline/content.wiki.twig'));
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

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $this->moveChildAtEnd($view, 'create');
    }

}
