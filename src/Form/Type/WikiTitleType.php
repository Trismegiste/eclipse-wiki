<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use App\Entity\Vertex;
use App\Validator\UniqueVertexTitle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * A title of a Vertex compatible with Parsoid
 */
class WikiTitleType extends AbstractType
{

    public function getParent(): string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [
                new Regex(Vertex::FORBIDDEN_REGEX_TITLE, match: false),
                new NotBlank(),
                new UniqueVertexTitle()
            ],
            'attr' => ['data-autofocus' => null]
        ]);
    }

}
