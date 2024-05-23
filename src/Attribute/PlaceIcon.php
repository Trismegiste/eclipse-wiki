<?php

/*
 * eclipse-wiki
 */

namespace App\Attribute;

use App\Entity\Place;
use App\Entity\Vertex;
use Attribute;

/**
 * Icon for Place
 */
#[Attribute(Attribute::TARGET_CLASS)]
class PlaceIcon extends Icon
{

    public function getName(Vertex $v): string
    {
        if (!$v instanceof Place) {
            return $this->name;
        }

        return ($v->world === 'Simulespace') ? 'simulspace' : 'place';
    }

}
