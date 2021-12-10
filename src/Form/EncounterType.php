<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Place;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for creating and editing a Encounter
 */
class EncounterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['edit']) {
            $builder->remove('title');
            $builder->setMethod('PUT');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new \App\Entity\Encounter($form->get('title')->getData());
        });
        $resolver->setDefault('edit', false);
    }

    public function getParent()
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['edit']) {
            $view['create']->vars['label'] = 'Edit';
        }
        $view['content']->vars['attr']['rows'] = 5;
        parent::finishView($view, $form, $options);
    }

    private function moveAtEnd(array &$arr, string $key): void
    {
        $item = $arr[$key];
        unset($arr[$key]);
        array_push($arr, $item);
    }

}
