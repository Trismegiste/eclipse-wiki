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

    public function getParent(): ?string
    {
        return VertexType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('gm_info', Type\WikitextType::class, [
                    'required' => false,
                    'attr' => ['rows' => 10],
                    'property_path' => 'gmInfo'
                ])
                ->add('target', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Handout::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Handout($form->get('title')->getData());
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $this->moveChildAtBegin($view, 'target');
        $this->moveChildAtEnd($view, 'create');
    }

}
