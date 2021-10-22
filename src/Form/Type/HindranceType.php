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
 * Choice for an Hindrance
 */
class HindranceType extends AbstractType implements \Symfony\Component\Form\DataMapperInterface
{

    protected $repository;

    public function __construct(\App\Repository\HindranceProvider $repo)
    {
        $this->repository = $repo;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', \App\Entity\Hindrance::class);
        $resolver->setDefault('expanded', false);
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
                    'expanded' => $options['expanded']
                ])
                ->add('level', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                    'choices' => [
                        'Mineur' => 1,
                        'Majeur' => 2
                    ],
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
            'Morphe',
            'Donné par le MJ'
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
        if (!$viewData instanceof \App\Entity\Hindrance) {
            throw new \Symfony\Component\Form\Exception\UnexpectedTypeException($viewData, \App\Entity\Hindrance::class);
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        $forms['name']->setData($viewData->getName());
        $forms['origin']->setData($viewData->origin);
        $forms['level']->setData($viewData->getLevel());
    }

    public function mapFormsToData(\Traversable $forms, &$edge)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $edge = $this->repository->findOne($forms['name']->getData());
        $edge->origin = $forms['origin']->getData();
        $edge->setLevel($forms['level']->getData());
    }

}
