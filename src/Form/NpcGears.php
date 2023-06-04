<?php

/*
 * Eclipse Wiki
 */

namespace App\Form;

use App\Entity\Character;
use App\Entity\Gear;
use App\Repository\GearProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Gears owned by a NPC
 */
class NpcGears extends AbstractType
{

    protected $provider;

    public function __construct(GearProvider $pro)
    {
        $this->provider = $pro;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gear_list', ChoiceType::class, [
                'placeholder' => '-------------',
                'mapped' => false,
                'required' => false,
                'choices' => $this->provider->getListing(),
                'choice_value' => function (?Gear $item) {
                    return json_encode($item);
                },
                'choice_label' => function (?Gear $item) {
                    return $item->getName();
                },
                'attr' => ['x-on:change' => 'gears.push(JSON.parse($event.target.value)); $el.value=""']
            ])
            ->add('gears', CollectionType::class, [
                'entry_type' => Type\GearType::class,
                'allow_add' => true,
                'allow_delete' => true
            ])
            ->add('edit', SubmitType::class)
            ->setMethod('PUT');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Character::class);
    }

}
