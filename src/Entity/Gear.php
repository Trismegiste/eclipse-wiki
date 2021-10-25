<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * A gear or stuff
 */
class Gear implements Indexable, Persistable
{

    use PersistableImpl;

    protected $name;

    public function getUId(): string
    {
        return $this->getName();
    }

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

}
