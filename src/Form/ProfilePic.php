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
        /** @var Character $npc */
        $npc = $options['data'];
        $defaultPic = $npc->tokenPic;
        if (!is_null($defaultPic)) {
            $defaultPic = $this->router->generate('get_picture', ['title' => $defaultPic]);
        }

        // for multiavatar
        $multiavatar = [$npc->getTitle() => $npc->getTitle()];
        for ($k = 1; $k < 15; $k++) {
            $val = $npc->getTitle() . rand();
            $multiavatar[$val] = $val;
        }

        $builder
                ->add('avatar', AvatarType::class, ['default_picture' => $defaultPic])
                ->add('multicultural', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                    'mapped' => false,
                    'required' => false,
                    'choices' => $multiavatar,
                    'block_prefix' => 'multicultural_avatar'
                ])
                ->add('generate', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Character::class);
    }

}
