<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Traversable;

/**
 * A roll for damage in SaWo
 */
class DamagePoolType extends AbstractType implements DataMapperInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pool', TextType::class)
            ->add('bonus', IntegerType::class)
            ->setDataMapper($this);
    }

    public function mapDataToForms($viewData, Traversable $forms)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $forms['pool']->setData(implode(' ', $viewData['pool']));
        $forms['bonus']->setData($viewData['bonus']);
    }

    public function mapFormsToData(Traversable $forms, &$viewData)
    {
        $forms = iterator_to_array($forms);
        $viewData = [
            'pool' => explode(' ', $forms['pool']->getData()),
            'bonus' => $forms['bonus']->getData()
        ];
    }

}
