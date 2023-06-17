<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Timeline;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Timeline with tree form
 */
class TimelineType extends AbstractType
{

    use FormTypeUtils;

    public function getParent(): string
    {
        return VertexType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('content');
        $builder->add('tree', Type\WikiTreeType::class, empty($options['data']) ? [] : ['state_key' => (string) $options['data']->getPk()]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Timeline($form->get('title')->getData());
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $this->moveChildAtEnd($view, 'create');
        $this->changeLabel($view, 'create', 'Save all labels');
    }

}
