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
 * A choice type with a list of Place
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
            'choice_label' => function (?Place $obj) {
                return $obj->getTitle();
            },
            'choice_value' => 'pk',
            'group_by' => function ($obj, $key, $value) {
                if (!empty($obj->battleMap)) {
                    return 'WITH_BATTLEMAP';
                }

                return 'WITHOUT_BATTLEMAP';
            }
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

}
