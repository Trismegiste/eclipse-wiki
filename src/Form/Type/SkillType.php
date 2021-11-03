<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Skill;
use App\Repository\SkillProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

/**
 * Value for a Skill
 */
class SkillType extends AbstractType implements DataMapperInterface
{

    protected $repository;

    public function __construct(SkillProvider $repo)
    {
        $this->repository = $repo;
    }

    public function getParent()
    {
        return SaWoTraitType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Skill::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return $this->repository->findOne($form->get('name')->getData());
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', HiddenType::class)
            ->setDataMapper($this)
        ;
    }

    public function mapDataToForms($viewData, Traversable $forms)
    {
        if (is_null($viewData)) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof Skill) {
            throw new UnexpectedTypeException($viewData, Skill::class);
        }

        $fields = iterator_to_array($forms);
        $fields['name']->setData($viewData->getName());
    }

    public function mapFormsToData(Traversable $forms, &$viewData)
    {
        $fields = iterator_to_array($forms);
        $viewData = $this->repository->findOne($fields['name']->getData());
        $viewData->dice = $fields['roll']->getData();
    }

}
