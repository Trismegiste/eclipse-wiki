<?php

/*
 * Eclipse Wiki
 */

namespace App\Voronoi\Shape;

/**
 * Drawing the global shape of the voronoi map
 */
abstract class Strategy implements \MongoDB\BSON\Persistable
{

    use \Trismegiste\Strangelove\MongoDb\PersistableImpl;

    abstract public function draw(\App\Voronoi\MapDrawer $draw): void;

    abstract function getName(): string;
}
