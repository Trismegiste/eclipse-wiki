<?php

/*
 * Eclipse Wiki
 */

namespace App\Voronoi\Shape;

use App\Voronoi\HexaCell;
use App\Voronoi\MapBuilder;
use App\Voronoi\MapDrawer;

/**
 * Encloses the map with a border
 */
class Border extends Strategy
{

    public function draw(MapDrawer $draw): void
    {
        $filling = new HexaCell(MapBuilder::VOID_UID, 'void', false);
        $draw->drawFrame($filling);
    }

    public function getName(): string
    {
        return 'STRAT_BORDER';
    }

}
