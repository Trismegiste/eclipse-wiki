<?php

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

class TileNpcConfig implements Persistable
{
    use PersistableImpl;

    public string $npcTitle;
    public string $tileLabel;
    public int $tilePerNpc;
}
