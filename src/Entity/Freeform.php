<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * a free form character : no faction, no shell, nor background
 */
class Freeform extends Character
{

    public function getDescription(): string
    {
        return $this->morph->type;
    }

}
