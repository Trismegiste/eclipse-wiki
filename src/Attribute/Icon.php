<?php

/*
 * Eclipse Wiki
 */

namespace App\Attribute;

use App\Entity\Vertex;
use Attribute;

/**
 * How a Vertex is viewed
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Icon
{

    public function __construct(protected string $name)
    {
        
    }

    public function getName(Vertex $v): string
    {
        return $this->name;
    }

}
