<?php

/*
 * eclipse-wiki
 */

namespace App\MapLayer;

use Trismegiste\MapGenerator\Procedural\GenericAutomaton;
use Trismegiste\MapGenerator\SvgPrintable;

/**
 * Axial symmetry layer
 */
class AxialSymmetry implements SvgPrintable
{

    protected $automat;

    public function __construct(GenericAutomaton $map)
    {
        $this->automat = $map;
    }

    public function duplicate(): void
    {
        $side = $this->automat->getSize();
        $grid = $this->automat->getGrid();
        for ($y = 0; $y < $side; $y++) {
            for ($x = 0; $x < $side / 2; $x++) {
                $this->automat->set($side - $x, $y, $grid[$x][$y]);
            }
        }
    }

    public function printSvg(): void
    {
        
    }

}
