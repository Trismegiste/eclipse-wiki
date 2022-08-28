<?php

/*
 * Eclipse Wiki
 */

namespace App\Voronoi\Shape;

/**
 * Null object
 */
class NullShape extends Strategy
{

    public function draw(\App\Voronoi\MapDrawer $draw): void
    {
        
    }

    public function getName(): string
    {
        return 'STRAT_NULL';
    }

}
