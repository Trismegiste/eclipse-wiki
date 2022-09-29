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
 * Description of PictogramType
 *
 * @author florent
 */
class PictogramType extends AbstractType
{

    protected PictoProvider $repository;

    public function __construct(PictoProvider $provider)
    {
        $this->repository = $provider;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choices', $this->repository->findAll());
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

}
