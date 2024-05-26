<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Scene;
use App\Form\Type\SceneContentWizardType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * It's a template for creating a scene
 */
class SceneCreate extends AbstractType
{

    use FormTypeUtils;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', SceneContentWizardType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            $title = $form->get('title')->getData();
            return (!is_null($title)) ? new Scene($title) : null;
        });
    }

    public function getParent(): ?string
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $this->moveChildAtEnd($view, 'create');
    }

}
