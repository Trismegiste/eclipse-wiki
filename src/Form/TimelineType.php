<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Timeline;
use App\Form\Type\WikitextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $builder
                ->add('elevatorPitch', WikitextType::class, ['attr' => ['rows' => 4]])
                ->add('tree', Type\WikiTreeType::class, empty($options['data']) ? [] : ['state_key' => (string) $options['data']->getPk()])
                ->add('debriefing', WikitextType::class, ['required' => false, 'attr' => ['rows' => 6]])
                ->add('update_stay', SubmitType::class)
        ;
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
        if (!empty($options['data'])) {
            $this->changeLabel($view, 'create', 'Save labels and view');
        }
    }

}
