<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Shape;

use App\Voronoi\HexaCell;
use App\Voronoi\MapBuilder;
use App\Voronoi\MapDrawer;

/**
 * Build a starship
 */
class Starship extends Strategy
{

    //put your code here
    public function draw(MapDrawer $draw): void
    {
        $size = $draw->getCanvasSize();
        $polygon = [
            [1, (int) 3 * $size / 7],
            [1, (int) 4 * $size / 7],
            [(int) $size / 2, 4 * $size / 5],
            [$size - 2, (int) 3 * $size / 4],
            [$size - 2, (int) $size / 4],
            [(int) $size / 2, $size / 5],
        ];

        $draw->fillOutsidePolygon(new HexaCell(MapBuilder::VOID_UID, 'void', false), $polygon);
    }

    public function getName(): string
    {
        return 'STRAT_STARSHIP';
    }

}
