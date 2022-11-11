<?php

/*
 * Eclipse Wiki
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * A text area widget with wikitext autocomplete link
 */
class WikitextType extends AbstractType
{

    public function getParent(): ?string
    {
        return TextareaType::class;
    }

}
