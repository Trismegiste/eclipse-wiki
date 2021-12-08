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
 * Form for creating a Place
 */
class PlaceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('youtubeUrl', Type\YoutubeType::class, [
            'required' => false,
            'attr' => ['class' => 'pure-input-1-2']
        ]);

        if ($options['edit']) {
            $builder->remove('title');
            $builder->setMethod('PUT');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Place($form->get('title')->getData());
        });
        $resolver->setDefault('edit', false);
    }

    public function getParent()
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $item = $view['youtubeUrl'];
        unset($view['youtubeUrl']);
        array_splice($view->children, 1, 0, [$item]);
    }

}
