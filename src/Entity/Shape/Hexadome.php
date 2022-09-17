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
        $xradius = $size / 2 - 1;
        $yradius = $xradius * (2 / sqrt(3));
        $polygon = [];
        for ($k = 0; $k < 6; $k++) {
            $polygon[] = [(int) round($center + $xradius * cos(2 * M_PI * $k / 6)), (int) round($center - $yradius * sin(2 * M_PI * $k / 6))];
        }

        $draw->fillOutsidePolygon(new HexaCell(HexaCell::VOID_UID, 'void', false), $polygon);
    }

    public function getName(): string
    {
        return 'STRAT_HEXADOME';
    }

}
