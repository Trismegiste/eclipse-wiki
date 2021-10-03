<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Repository\BackgroundProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of Npc
 */
class Npc extends AbstractType
{

    protected $bgRepo;

    public function __construct(BackgroundProvider $bg)
    {
        $this->bgRepo = $bg;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('historique', ChoiceType::class, ['choices' => $this->bgRepo->getListing()])
            ->add('generate', SubmitType::class);
    }

}
