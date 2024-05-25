<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Transhuman;
use App\Form\Type\ProviderChoiceType;
use App\Form\Type\SurnameLanguageType;
use App\Form\Type\WikitextType;
use App\Form\Type\WikiTitleType;
use App\Repository\BackgroundProvider;
use App\Repository\CharacterFactory;
use App\Repository\FactionProvider;
use App\Repository\MorphProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('wildCard', CheckboxType::class, ['required' => false])
                ->add('title', WikiTitleType::class, ['attr' => ['placeholder' => 'Choisissez un nom']])
                ->add('background', ProviderChoiceType::class, ['provider' => $this->background, 'placeholder' => '--- Choisissez un Historique ---'])
                ->add('faction', ProviderChoiceType::class, ['provider' => $this->faction, 'placeholder' => '--- Choisissez une Faction ---'])
                ->add('morph', ProviderChoiceType::class, ['provider' => $this->morph, 'placeholder' => '--- Choisissez un Morphe ---'])
                ->add('surnameLang', SurnameLanguageType::class)
                ->add('content', WikitextType::class, ['required' => false])
                ->add('generate', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Transhuman::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            $bg = $form->get('background')->getData();
            $fac = $form->get('faction')->getData();
            $name = $form->get('title')->getData();

            if (!is_null($name) && !is_null($bg) && !is_null($fac)) {
                return $this->factory->create($name, $bg, $fac);
            }

            return null;
        });
    }

    public function getBlockPrefix(): string
    {
        return 'npc';
    }

}
