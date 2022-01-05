<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Handout;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for Handout entity
 */
class HandoutType extends AbstractType
{

    use FormTypeUtils;

    public function getParent()
    {
        return VertexType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('gm_info', TextareaType::class, [
                    'required' => false,
                    'attr' => ['rows' => 10],
                    'property_path' => 'gmInfo'
                ])
                ->add('target', TextType::class);

        if ($options['edit']) {
            $builder->setMethod('PUT');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('edit', false);
        $resolver->setDefault('data_class', Handout::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Handout($form->get('title')->getData());
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->moveChildAtBegin($view, 'target');
        $this->moveChildAtEnd($view, 'create');

        if ($options['edit']) {
            $this->changeLabel($view, 'create', 'Edit');
        }
    }

}
