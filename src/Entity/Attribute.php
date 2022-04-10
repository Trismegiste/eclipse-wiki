<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * A SaWo Attribute
 */
class Attribute extends SaWoTrait implements Persistable, \JsonSerializable
{

    use PersistableImpl;

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'dice' => $this->dice,
            'modifier' => $this->modifier
        ];
    }

}
