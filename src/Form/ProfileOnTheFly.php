<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Transhuman;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form for generating a quick npc profile
 */
class ProfileOnTheFly extends AbstractType
{

    protected $bauhausAvatar;

    public function __construct(\App\Service\BoringAvatar $maker)
    {
        $this->bauhausAvatar = $maker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('avatar', FileType::class, ['mapped' => false])
                ->add('title', TextType::class)
                ->add('generate', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Transhuman::class);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $bauhaus = [];
        for ($k = 0; $k < 24; $k++) {
            $bauhaus[] = $this->bauhausAvatar->createBauhaus('yolo' . rand());
        }
        $view->vars['bauhaus'] = $bauhaus;
    }

}
