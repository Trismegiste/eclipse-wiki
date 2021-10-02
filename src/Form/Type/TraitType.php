<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Repository\TraitProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * One trait choice
 */
class TraitType extends AbstractType
{

    protected $repository;

    public function __construct(TraitProvider $repo)
    {
        $this->repository = $repo;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('category');

        $resolver->setDefault('choices', function (Options $opt) {

            return [
                'Attributs' => $this->repository->findAttributes(),
                'Compétences' => $this->repository->findSkills(),
                'Réseaux sociaux' => $this->repository->findSocialNetworks()
            ];
        });
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

}
