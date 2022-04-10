<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * Faction for a NPC
 */
class Faction implements Indexable, Persistable
{

    use PersistableImpl;

    public $title;
    public $characteristic = [];
    public $motivation = [];

    public function __construct(string $param)
    {
        $this->title = $param;
    }

    public function getUId(): string
    {
        return $this->title;
    }

}
