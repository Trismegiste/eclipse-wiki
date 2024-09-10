<?php

/*
 * eclipse-wiki
 */

namespace App\Form\QuickNpc;

use App\Entity\Skill;
use App\Form\Type\SaWoTraitMapper;
use App\Repository\SkillProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Result stats
 */
class SkillHiddenStat extends AbstractType
{

    public function __construct(protected SkillProvider $provider)
    {
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Skill::class);
        $resolver->setDefault(
                'empty_data', function (FormInterface $form) {
                        return $this->provider->findOne($form->get('name')->getData());
                });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('name', HiddenType::class)
                ->add('roll', HiddenType::class);

        $builder->setDataMapper(new SaWoTraitMapper($this->provider));
    }

}
