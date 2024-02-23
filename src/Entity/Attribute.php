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

    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->name,
            'dice' => $this->dice,
            'modifier' => $this->modifier
        ];
    }

    /**
     * 3-letter abbreviation of this Attribute
     * @return string
     */
    public function getAbbrev(): string
    {
        return mb_substr(mb_strtoupper($this->name), 0, 3);
    }

}
