<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\Edge;
use App\Repository\EdgeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Choice for an Edge
 */
class EdgeType extends AbstractType implements \Symfony\Component\Form\DataMapperInterface
{

    protected $repository;

    public function __construct(EdgeProvider $repo)
    {
        $this->repository = $repo;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Edge::class);
        $resolver->setDefault('expanded', false);
        $resolver->setDefault('preferred_choices', ['Progression']);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return $this->repository->findOne($form->get('name')->getData());
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', HiddenType::class, ['mapped' => 0])
                ->add('origin', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                    'choices' => $this->getOrigin(),
                    'preferred_choices' => $options['preferred_choices'],
                    'expanded' => $options['expanded']
                ])
                ->setDataMapper($this)
        ;
    }

    protected function getOrigin(): array
    {
        $src = [
            'Background',
            'Faction',
            'Création',
            'Progression',
            'Cadeau',
            'Morphe',
            'Slot'
        ];

        return array_combine($src, $src);
    }

    public function mapDataToForms($viewData, \Traversable $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof Edge) {
            throw new \Symfony\Component\Form\Exception\UnexpectedTypeException($viewData, Color::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['name']->setData($viewData->getName());
        $forms['origin']->setData($viewData->origin);
    }

    public function mapFormsToData(\Traversable $forms, &$edge)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $edge = $this->repository->findOne($forms['name']->getData());
        $edge->origin = $forms['origin']->getData();
    }

}
