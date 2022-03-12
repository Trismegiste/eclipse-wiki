<?php

/*
 * Eclipse Wiki
 */

namespace App\MapLayer;

use Trismegiste\MapGenerator\Procedural\GenericAutomaton;
use Trismegiste\MapGenerator\SvgPrintable;

/**
 * HexGrid layer draws a flat-top hexmap grid
 */
class HexGrid implements SvgPrintable
{

    protected $automat;
    protected $scale;

    public function __construct(GenericAutomaton $map, float $scale = 0.25)
    {
        $this->automat = $map;
        $this->scale = $scale;
    }

    public function printSvg(): void
    {
        $sin60 = sin(M_PI / 3);
        $doubleSin60 = 2 * $sin60;
        $scale = $this->scale;
        $side = $this->automat->getSize() / $scale;
        echo <<<YOLO
            <defs>
                <pattern id="hexmap" x="0" y="0" width="3" height="$doubleSin60" patternUnits="userSpaceOnUse">
                    <g style="stroke: black; stroke-width: 0.05" stroke-opacity="0.3">
                        <path d="M 0 0 h 1" transform="translate(1,0) rotate(60)"/>
                        <path d="M 0 0 h 1" transform="translate(1,$doubleSin60) rotate(-60)"/>
                        <path d="M 1.5 $sin60 h 1 M 0 0 h 1" />
                        <path d="M 0 0 h 1" transform="translate(2.5,$sin60) rotate(-60)"/>
                        <path d="M 0 0 h 1" transform="translate(2.5,$sin60) rotate(60)"/>
                    </g>
                </pattern>
            </defs>
        
            <rect fill="url(#hexmap)" stroke="none" width="$side" height="$side" transform="scale($scale,$scale)"/>
YOLO;
    }

}
