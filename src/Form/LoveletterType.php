<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Loveletter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Love letter form type
 */
class LoveletterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('player', TextType::class, ['attr' => ['class' => 'pure-input-1-3']])
            ->add('drama', TextareaType::class, ['attr' => ['rows' => 3]])
            ->add('roll1', Type\RollType::class)
            ->add('roll2', Type\RollType::class)
            ->add('roll3', Type\RollType::class)
            ->add('resolution', \Symfony\Component\Form\Extension\Core\Type\CollectionType::class, [
                'entry_type' => TextareaType::class,
                'allow_add' => true
            ])
        ;

        if ($options['edit']) {
            $builder->setMethod('PUT');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Loveletter($form->get('title')->getData());
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
        $view['content']->vars['attr']['rows'] = 3;
        $view['content']->vars['label'] = 'Context';
        parent::finishView($view, $form, $options);
        $this->moveAtEnd($view->children, 'create');
        $this->moveAtStart($view->children, 'player');
    }

    private function moveAtEnd(array &$arr, string $key): void
    {
        $item = $arr[$key];
        unset($arr[$key]);
        array_push($arr, $item);
    }

    private function moveAtStart(array &$arr, string $key): void
    {
        $item = $arr[$key];
        unset($arr[$key]);
        array_unshift($arr, $item);
    }

}
