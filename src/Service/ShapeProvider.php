<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Shape\Border;
use App\Entity\Shape\Dome;
use App\Entity\Shape\Hexadome;
use App\Entity\Shape\NullShape;
use App\Entity\Shape\Starship;
use App\Entity\Shape\SvgStrategy;
use App\Entity\Shape\Torus;

/**
 * Repository for Shape Strategies
 */
class ShapeProvider
{

    protected string $path;

    public function __construct(string $basepath)
    {
        $this->path = $basepath;
    }

    public function findAll(): array
    {
        return [
            new NullShape(),
            new Border(),
            new Dome(),
            new Torus(),
            new Starship(),
            new Hexadome(),
            new SvgStrategy('vaisseau', file_get_contents($this->path . '/vaisseau.svg'))
        ];
    }

}
