<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Repository\BackgroundProvider;
use App\Repository\FactionProvider;
use App\Repository\MorphProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of Npc
 */
class Npc extends AbstractType
{

    protected $background;
    protected $faction;
    protected $morph;

    public function __construct(BackgroundProvider $bg, FactionProvider $fac, MorphProvider $morph)
    {
        $this->background = $bg;
        $this->faction = $fac;
        $this->morph = $morph;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('historique', Type\ProviderChoiceType::class, ['provider' => $this->background, 'placeholder' => '--- Choisissez un Historique ---'])
                ->add('faction', Type\ProviderChoiceType::class, ['provider' => $this->faction, 'placeholder' => '--- Choisissez une Faction ---'])
                ->add('morphe', Type\ProviderChoiceType::class, ['provider' => $this->morph, 'placeholder' => '--- Choisissez un Morphe ---'])
                ->add('generate', SubmitType::class);
    }

}
