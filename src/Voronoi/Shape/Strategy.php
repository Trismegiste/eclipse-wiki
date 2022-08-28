<?php

/*
 * Eclipse Wiki
 */

namespace App\Voronoi\Shape;

use App\Voronoi\MapDrawer;
use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * Drawing the global shape of the voronoi map
 */
abstract class Strategy implements Persistable
{

    use PersistableImpl;

    abstract public function draw(MapDrawer $draw): void;

    abstract function getName(): string;
}
