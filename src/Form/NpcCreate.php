<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use App\Repository\BackgroundProvider;
use App\Repository\CharacterFactory;
use App\Repository\FactionProvider;
use App\Repository\MorphProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Creation of Npc
 */
class NpcCreate extends AbstractType
{

    protected $background;
    protected $faction;
    protected $morph;
    protected $factory;

    public function __construct(BackgroundProvider $bg, FactionProvider $fac, MorphProvider $morph, CharacterFactory $factory)
    {
        $this->background = $bg;
        $this->faction = $fac;
        $this->morph = $morph;
        $this->factory = $factory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['placeholder' => 'Choisissez un nom']])
            ->add('background', Type\ProviderChoiceType::class, ['provider' => $this->background, 'placeholder' => '--- Choisissez un Historique ---'])
            ->add('faction', Type\ProviderChoiceType::class, ['provider' => $this->faction, 'placeholder' => '--- Choisissez une Faction ---'])
            ->add('morph', Type\ProviderChoiceType::class, ['provider' => $this->morph, 'placeholder' => '--- Choisissez un Morphe ---'])
            ->add('generate', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Character::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            $bg = $form->get('background')->getData();
            $fac = $form->get('faction')->getData();

            if (!is_null($bg) && !is_null($fac)) {
                return $this->factory->create($bg, $fac);
            }

            return null;
        });
    }

    public function getBlockPrefix()
    {
        return 'npc';
    }

}
