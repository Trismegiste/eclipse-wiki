<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Trismegiste\NameGenerator\FileRepository;
use Trismegiste\NameGenerator\RandomizerDecorator;

/**
 * Generates multiple names
 */
class HumanNameType extends AbstractType
{

    protected $repository;

    public function __construct()
    {
        $this->repository = new RandomizerDecorator(new FileRepository());
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('language');
        $resolver->setDefault('name_number', 24);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $listing = [];
        foreach (['female', 'male'] as $gender) {
            for ($k = 0; $k < $options['name_number']; $k++) {
                $lang = random_int(0, 100) < 75 ? $options['language'] : 'random';
                $listing[$gender][] = $this->repository->getRandomGivenNameFor($gender, 'random') . ' ' . $this->repository->getRandomSurnameFor($lang);
            }
        }

        $view->vars['choices'] = $listing;
    }

}
