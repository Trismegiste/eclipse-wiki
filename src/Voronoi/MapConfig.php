<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use Trismegiste\Strangelove\MongoDb\Root;
use Trismegiste\Strangelove\MongoDb\RootImpl;

/**
 * Config entity for HexaMap
 */
class MapConfig implements Root
{

    use RootImpl;
}
