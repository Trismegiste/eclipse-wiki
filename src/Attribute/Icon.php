<?php

/*
 * Eclipse Wiki
 */

namespace App\Attribute;

use Attribute;

/**
 * How a Vertex is viewed
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Icon
{

    public function __construct(public string $name)
    {
        
    }

}
