<?php

/*
 * eclipse-wiki
 */

namespace App\Form\CreationDag;

use App\Entity\CreationTree\Graph;
use App\Entity\CreationTree\Node;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

/**
 * Description of DeleteNode
 *
 * @author trismegiste
 */
class DeleteNode extends AbstractType implements DataMapperInterface
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('delete', SubmitType::class)
                ->setDataMapper($this)
                ->setMethod('delete');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Graph::class)
                ->setRequired('selected')
                ->setAllowedTypes('selected', Node::class);
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        
    }

    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        $widget = iterator_to_array($forms);
        $toDelete = $widget['delete']->getParent()->getConfig()->getOption('selected');
        $viewData->deleteNodeAndLinks($toDelete);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['selected'] = $options['selected'];
    }

}
