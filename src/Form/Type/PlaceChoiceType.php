<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Place;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of PlaceChoiceType
 */
class PlaceChoiceType extends AbstractType
{

    protected $repo;

    public function __construct(VertexRepository $repo)
    {
        $this->repo = $repo;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->repo->findByClass(Place::class),
            'choice_label' => 'title',
            'choice_value' => 'pk',
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

}
