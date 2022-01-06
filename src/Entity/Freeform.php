<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Description of Freeform
 *
 * @author flo
 */
class Freeform extends Character
{

    public function getDescription(): string
    {
        return $this->morph->type;
    }

}
