<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use App\Attribute\Icon;

/**
 * a free form character : no faction, no shell, nor background
 */
#[Icon('monster')]
class Freeform extends Character
{

    public function getDescription(): string
    {
        return $this->morph->type;
    }

}
