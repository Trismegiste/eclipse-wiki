<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Loveletter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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

    use FormTypeUtils;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('player', TextType::class, ['attr' => ['class' => 'pure-input-1-3']])
                ->add('drama', Type\WikitextType::class, ['attr' => ['rows' => 3]])
                ->add('roll1', Type\RollType::class)
                ->add('roll2', Type\RollType::class)
                ->add('roll3', Type\RollType::class)
                ->add('resolution', CollectionType::class, [
                    'entry_type' => TextareaType::class,
                    'allow_add' => true
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Loveletter($form->get('title')->getData());
        });
    }

    public function getParent(): ?string
    {
        return VertexType::class;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->changeAttribute($view, 'content', 'rows', 3);
        $this->changeLabel($view, 'content', 'Context');
        $this->moveChildAtBegin($view, 'player');
        $this->moveChildAtEnd($view, 'create');
    }

}
