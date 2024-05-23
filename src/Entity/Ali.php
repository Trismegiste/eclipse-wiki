<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use App\Attribute\Icon;

/**
 * Ali is an artificial limited intelligence
 */
#[Icon('ali')]
class Ali extends Character
{

    public function getDescription(): string
    {
        return 'IAL';
    }

}
