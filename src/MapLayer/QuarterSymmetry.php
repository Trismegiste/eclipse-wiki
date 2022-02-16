<?php

/*
 * eclipse-wiki
 */

namespace App\MapLayer;

use Trismegiste\MapGenerator\Procedural\GenericAutomaton;
use Trismegiste\MapGenerator\SvgPrintable;

/**
 * Duplicate the top-left quarter onto the 3 other corners
 */
class QuarterSymmetry implements SvgPrintable
{

    protected $automat;

    public function __construct(GenericAutomaton $map)
    {
        $this->automat = $map;
    }

    public function printSvg(): void
    {
        
    }

    public function duplicate(): void
    {
        $side = $this->automat->getSize();
        $grid = $this->automat->getGrid();
        for ($y = 0; $y < $side / 2; $y++) {
            for ($x = 0; $x < $side / 2; $x++) {
                $cell = $grid[$x][$y];
                $this->automat->set($side - $x, $y, $cell);
                $this->automat->set($x, $side - $y, $cell);
                $this->automat->set($side - $x, $side - $y, $cell);
            }
        }
    }

}
