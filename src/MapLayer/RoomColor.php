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

    const timeout = 0.1; // one tenth of second

    protected $automat;
    protected $picked = [];

    public function __construct(GenericAutomaton $map)
    {
        $this->automat = $map;
    }

    /**
     * Searching rooms and randomly adding colors. The number of rooms are in SQUARES.
     * If you give ['blue'=>10] it could be one room of 10 squares or 10 rooms of a 1 square
     * 
     * Note : it should be noted that, depending of the map, this method could fail.
     * That's why there is a timeout to prevent infinite loop : self::timeout
     * @param array $howManyPerColor a associative array of [color => how many squares]
     */
    public function generate(array $howManyPerColor): void
    {
        $group = $this->automat->getSquaresPerRoomPerLevel();
        $flatten = [];
        foreach ($group as $roomPerLevel) {
            foreach ($roomPerLevel as $room) {
                $flatten[] = $room;
            }
        }

        foreach ($howManyPerColor as $color => $howMany) {
            $current = 0;
            $this->picked[$color] = [];
            $deadline = microtime(true) + self::timeout;
            while (($current < $howMany) && (microtime(true) < $deadline)) {
                $idx = rand(0, count($flatten) - 1);
                if (($current + count($flatten[$idx])) <= $howMany) {
                    $this->picked[$color][] = $flatten[$idx];
                    $current += count($flatten[$idx]);
                    array_splice($flatten, $idx, 1);
                }
            }
        }
    }

    public function printSvg(): void
    {
        foreach ($this->picked as $color => $roomList) {
            foreach ($roomList as $room) {
                echo '<g fill="' . $color . '" class="hilite-room" fill-opacity="33%">';
                foreach ($room as $square) {
                    $x = $square['x'];
                    $y = $square['y'];
                    echo "<rect x=\"$x\" y=\"$y\" width=\"1\" height=\"1\"/>";
                }
                echo '</g>';
            }
        }
    }

}
