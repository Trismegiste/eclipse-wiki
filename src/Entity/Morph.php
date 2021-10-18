<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * Morph for a NPC
 */
class Morph implements Indexable, Persistable
{

    use PersistableImpl,
        EdgeContainer;

    public $title;
    public $ability = [];
    public $disability = [];
    public $type;
    public $price;

    public function __construct(string $param)
    {
        $this->title = $param;
    }

    public function getUId(): string
    {
        return $this->title;
    }

}
