<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Skill;
use App\Repository\SkillProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Value for a Skill
 */
class SkillType extends AbstractType
{

    protected $repository;

    public function __construct(SkillProvider $repo)
    {
        $this->repository = $repo;
    }

    public function getParent()
    {
        return SaWoTraitType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('provider', $this->repository);
        $resolver->setDefault('data_class', Skill::class);
    }

}
