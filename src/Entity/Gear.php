<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use JsonSerializable;
use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * A gear or stuff
 */
class Gear implements Indexable, Persistable, JsonSerializable
{

    use PersistableImpl;

    protected string $name;
    public $price;

    public function getUId(): string
    {
        return $this->getName();
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function jsonSerialize(): mixed
    {
        return $this->bsonSerialize();
    }

}
