<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Skill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of AttributeType
 */
class SkillType extends AbstractType
{

    public function getParent()
    {
        return SaWoTraitType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Skill::class);
    }

}
