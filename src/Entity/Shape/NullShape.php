<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity\Shape;

use App\Voronoi\MapDrawer;

/**
 * Null object
 */
class NullShape extends Strategy
{

    public function draw(MapDrawer $draw): void
    {
        
    }

    public function getName(): string
    {
        return 'STRAT_NULL';
    }

}
