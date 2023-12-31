<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use App\Form\Type\AvatarType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Creating the profile pic
 */
class ProfilePic extends AbstractType
{

    public function __construct(protected UrlGeneratorInterface $router)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $defaultPic = $options['data']->tokenPic;
        if (!is_null($defaultPic)) {
            $defaultPic = $this->router->generate('get_picture', ['title' => $defaultPic]);
        }

        $builder
                ->add('avatar', AvatarType::class, ['default_picture' => $defaultPic])
                ->add('generate', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Character::class);
    }

}
