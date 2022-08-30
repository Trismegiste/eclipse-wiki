<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity\Shape;

use App\Voronoi\HexaCell;
use App\Voronoi\MapBuilder;
use App\Voronoi\MapDrawer;

/**
 * Draw a torus
 */
class Torus extends Strategy
{

    public function draw(MapDrawer $draw): void
    {
        $filling = new HexaCell(MapBuilder::VOID_UID, 'void', false);
        $draw->drawTorusContainer($filling);
        $hallway = new HexaCell(MapBuilder::HALLWAY_UID, 'default', false);
        $draw->circle($hallway);
    }

    public function getName(): string
    {
        return 'STRAT_TORUS';
    }

}
