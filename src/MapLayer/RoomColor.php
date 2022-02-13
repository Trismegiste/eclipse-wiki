<?php

/*
 * Eclipse Wiki
 */

namespace App\MapLayer;

use Trismegiste\MapGenerator\Procedural\GenericAutomaton;
use Trismegiste\MapGenerator\SvgPrintable;

/**
 * Adds colors to rooms
 */
class RoomColor implements SvgPrintable
{

    protected $automat;
    protected $picked = [];

    public function __construct(GenericAutomaton $map)
    {
        $this->automat = $map;
    }

    public function generate(int $howMany): void
    {
        $group = $this->automat->getSquaresPerRoomPerLevel();
        $flatten = [];
        foreach ($group as $roomPerLevel) {
            foreach ($roomPerLevel as $room) {
                $flatten[] = $room;
            }
        }

        for ($current = 0; $current < $howMany; $current++) {
            $idx = rand(0, count($flatten) - 1);
            $this->picked[] = $flatten[$idx];
            array_splice($flatten, $idx, 1);
        }
    }

    public function printSvg(): void
    {
        foreach ($this->picked as $room) {
            echo '<g fill="blue" class="hilite-room" fill-opacity="33%">';
            foreach ($room as $square) {
                $x = $square['x'];
                $y = $square['y'];
                echo "<rect x=\"$x\" y=\"$y\" width=\"1\" height=\"1\"/>";
            }
            echo '</g>';
        }
    }

}
