<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Trismegiste\NameGenerator\FileRepository;

class SurnameLanguageType extends AbstractType
{
    protected $generator;

    public function __construct()
    {
        $this->generator = new FileRepository();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $tmpList = $this->generator->getSurnameLanguage();
        sort($tmpList);
        $language['-- Aléatoire --'] = 'random';
        foreach ($tmpList as $lang) {
            $language[ucfirst($lang)] = $lang;
        }

        $resolver->setDefaults([
            'choices' => $language,
            'placeholder' => '-------------',
            'required' => false
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
