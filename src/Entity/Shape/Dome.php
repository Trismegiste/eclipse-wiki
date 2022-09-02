<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity\Shape;

use App\Voronoi\HexaCell;
use App\Voronoi\MapDrawer;

/**
 * Drawing a Dome strategy
 */
class Dome extends Strategy
{

    public function draw(MapDrawer $draw): void
    {
        $filling = new HexaCell(HexaCell::VOID_UID, 'void', false);
        $draw->drawCircleContainer($filling);
    }

    public function getName(): string
    {
        return 'STRAT_DOME';
    }

}
