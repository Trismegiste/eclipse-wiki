<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Skill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of AttributeType
 */
class SkillType extends AbstractType
{
    public function __construct(\App\Repository\SkillProvider $repo)
    {
        
    }

    public function getParent()
    {
        return SaWoTraitType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Skill::class);
        $resolver->setDefault('empty_data', function (FormInterface $form) {
            return new Skill($form->get('name')->getData(), 'Undefined');
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', HiddenType::class);
    }

}
