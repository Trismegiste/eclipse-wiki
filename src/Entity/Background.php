<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * Background for a NPC
 */
class Background implements Indexable, Persistable
{

    use PersistableImpl;

    public $title;
    public $ability = [];
    public $disability = [];
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
