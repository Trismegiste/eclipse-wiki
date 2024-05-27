<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Subgraph;
use App\Form\Type\WikiTitleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for renaming a vertex
 * Work on a subgraph for renaming all inbound vertices that link to the focus vertex
 */
class VertexRename extends AbstractType implements \Symfony\Component\Form\DataMapperInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('title', WikiTitleType::class, [
                    'label' => 'New title',
                    'mapped' => false
                ])
                ->add('rename', SubmitType::class)
                ->setMethod('PUT')
                ->setDataMapper($this)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Subgraph::class);
    }

    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $field = iterator_to_array($forms);
        $field['title']->setData($viewData->getFocus()->getTitle());
    }

    public function mapFormsToData(\Traversable $forms, &$viewData): void
    {
        /** @var Subgraph $viewData */
        $field = iterator_to_array($forms);
        $newTitle = $field['title'];
        $viewData->renameFocused($newTitle->getData());
    }

}
