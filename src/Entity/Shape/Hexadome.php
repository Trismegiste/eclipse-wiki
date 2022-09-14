<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Shape;

use App\Voronoi\HexaCell;
use App\Voronoi\MapDrawer;

/**
 * Build a hexagonal dome
 */
class Hexadome extends Strategy
{

    public function draw(MapDrawer $draw): void
    {
        $size = $draw->getCanvasSize();
        $center = $size / 2;
        $radius = $size / 2 - 1;
        $polygon = [];
        for($k = 0; $k < 6; $k++) {
            $polygon[] = [intval($center + $radius * cos(2 * M_PI * $k / 6)), intval($center - $radius * sin(2 * M_PI * $k / 6))];
        }

        $draw->fillOutsidePolygon(new HexaCell(HexaCell::VOID_UID, 'void', false), $polygon);
    }

    public function getName(): string
    {
        return 'STRAT_HEXADOME';
    }

}
