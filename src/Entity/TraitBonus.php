<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * A bonus on a Trait
 */
class TraitBonus implements Persistable
{

    use PersistableImpl;

    public int $dieType;
    public int $flat;

    public function __construct(int $tdd, int $modifier = 0)
    {
        $this->dieType = $tdd;
        $this->flat = $modifier;
    }

}
