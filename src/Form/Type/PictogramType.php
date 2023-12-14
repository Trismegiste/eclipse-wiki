<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Service\PictoProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A choice of Pictogram (a SVG for battlemaps)
 */
class PictogramType extends AbstractType
{

    protected PictoProvider $repository;

    public function __construct(PictoProvider $provider)
    {
        $this->repository = $provider;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->repository->findAll(),
            'translation_domain' => 'pictogram'
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

}
