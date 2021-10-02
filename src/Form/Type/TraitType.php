<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Repository\TraitProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
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

        $resolver->setDefault('placeholder', '-----');

        $resolver->setDefault('choices', function (Options $opt) {
            switch ($opt['category']) {
                case 'attribute':
                    return $this->repository->findAttributes();
                case 'skill':
                    return $this->repository->findSkills();
                case 'socialnetwork':
                    return $this->repository->findSocialNetworks();
                case 'all':
                    return [
                        'Attributs' => $this->repository->findAttributes(),
                        'Compétences' => $this->repository->findSkills(),
                        'Réseaux sociaux' => $this->repository->findSocialNetworks()
                    ];
                default:
                    throw new InvalidConfigurationException("Unknown Traits category");
            }
        });
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

}
