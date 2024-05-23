<?php

/*
 * eclipse-wiki
 */

namespace App\Attribute;

use App\Entity\Transhuman;
use App\Entity\Vertex;
use Attribute;

/**
 * Icon for transhuman
 */
#[Attribute(Attribute::TARGET_CLASS)]
class TranshumanIcon extends Icon
{

    public function getName(Vertex $v): string
    {
        if (!$v instanceof Transhuman) {
            return $this->name;
        }

        if ($v->wildCard) {
            return 'wildcard';
        } else {
            return $v->isNpcTemplate() ? 'extra' : 'male';
        }
    }

}
