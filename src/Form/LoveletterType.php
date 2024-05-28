<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Loveletter;
use App\Form\Type\RollType;
use App\Form\Type\WikitextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Love letter form type
 */
class LoveletterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('content');
        $builder
                ->add('player', TextType::class, [
                    'attr' => ['class' => 'pure-input-1-3'],
                    'priority' => 2000
                ])
                ->add('context', WikitextType::class, ['attr' => ['rows' => 3]])
                ->add('drama', WikitextType::class, ['attr' => ['rows' => 3]])
                ->add('roll1', RollType::class)
                ->add('roll2', RollType::class)
                ->add('roll3', RollType::class)
                ->add('resolution', CollectionType::class, [
                    'entry_type' => TextareaType::class,
                    'allow_add' => true
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Loveletter($form->get('title')->getData());
        });
    }

    public function getParent(): ?string
    {
        return VertexType::class;
    }

}
