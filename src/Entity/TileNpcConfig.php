<?php

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * A configuration for texturing a battlemap with a tile
 */
class TileNpcConfig implements Persistable
{

    use PersistableImpl;

    public MapToken $npc;
    public int $tilePerNpc;

}
