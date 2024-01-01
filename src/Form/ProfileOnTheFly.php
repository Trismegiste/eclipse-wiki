<?php

/*
 * eclipse-wiki
 */

namespace App\Form;

use App\Entity\Transhuman;
use App\Form\Type\AvatarMultisourceType;
use App\Form\Type\AvatarType;
use App\Form\Type\HumanNameType;
use App\Repository\CharacterFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Form for generating a quick npc profile
 */
class ProfileOnTheFly extends AbstractType
{

    public function __construct(protected UrlGeneratorInterface $router, protected CharacterFactory $factory)
    {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Transhuman $npc */
        $npc = $options['transhuman'];
        $defaultPic = $npc->tokenPic;
        if (!is_null($defaultPic)) {
            $defaultPic = $this->router->generate('get_picture', ['title' => $defaultPic]);
        }

        $builder
                ->add('title', HumanNameType::class, [
                    'language' => $npc->surnameLang
                ])
                ->add('avatar', AvatarType::class, ['default_picture' => $defaultPic])
                ->add('multisource', AvatarMultisourceType::class, [
                    'local_pictures' => $npc->extractPicture(),
                    'random_prefix' => $npc->getTitle(),
                    'default_prompt' => $npc->tokenPicPrompt
                ])
                ->add('push_profile', SubmitType::class)
                ->add('instantiate_npc', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Transhuman::class);
        $resolver->setRequired('transhuman');
        $resolver->setAllowedTypes('transhuman', Transhuman::class);

        $resolver->setDefault('empty_data', function (Options $opt) {
            $template = $opt['transhuman'];
            return function (FormInterface $form) use ($template) {
                return $this->factory->createExtraFromTemplate($template, $form['title']->getData());
            };
        });
    }

}
