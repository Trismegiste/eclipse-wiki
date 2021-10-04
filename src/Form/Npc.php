<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Repository\BackgroundProvider;
use App\Repository\FactionProvider;
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

    public function __construct(BackgroundProvider $bg, FactionProvider $fac)
    {
        $this->background = $bg;
        $this->faction = $fac;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('historique', ChoiceType::class, ['choices' => $this->background->getListing(), 'placeholder' => '--- Choisissez un Historique ---'])
                ->add('faction', ChoiceType::class, ['choices' => $this->faction->getListing(), 'placeholder' => '--- Choisissez une Faction ---'])
                ->add('generate', SubmitType::class);
    }

}
