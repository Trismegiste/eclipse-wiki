<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Transhuman;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Creating the profile pic
 */
class ProfilePic extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('avatar', FileType::class, [
                'mapped' => false,
                'attr' => ['x-on:change' => 'readFile($el)']
            ])
            ->add('content', HiddenType::class)
            ->add('generate', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Transhuman::class);
    }

}
