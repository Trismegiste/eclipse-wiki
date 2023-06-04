<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Transhuman;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for generating a quick npc profile
 */
class ProfileOnTheFly extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('avatar', Type\AvatarChoiceType::class, [
                    'mapped' => false,
                    'bitmap' => $options['data']->tokenPic
                ])
                ->add('title', Type\HumanNameType::class, [
                    'data' => '',
                    'language' => $options['data']->surnameLang
                ])
                ->add('push_profile', SubmitType::class)
                ->add('instantiate_npc', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Transhuman::class);
    }

}
