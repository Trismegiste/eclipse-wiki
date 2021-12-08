<?php

/*
 * Vesta
 */

namespace App\Form\Type;

use App\Repository\FlatRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FullTextChoice is a choice type with a list where choices are 
 */
class FullTextChoice extends AbstractType
{

    protected $repository;

    public function __construct(FlatRepository $repo)
    {
        $this->repository = $repo;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('category');

        $resolver->setDefault('choices', function (Options $opt) {
            return $this->repository->findAll($opt['category']);
        });
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

}
