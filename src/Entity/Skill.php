<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * A SaWo Skill
 */
class Skill extends SaWoTrait implements Persistable, \JsonSerializable
{

    use PersistableImpl;

    protected $linkedAttr;
    protected $core;

    public function __construct(string $str, string $linkAttr, bool $core = false)
    {
        parent::__construct($str);
        $this->linkedAttr = $linkAttr;
        $this->core = $core;
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'dice' => $this->dice,
            'modifier' => $this->modifier
        ];
    }

    public function getLinkedAttribute(): string
    {
        return $this->linkedAttr;
    }

    public function isCore(): bool
    {
        return $this->core;
    }

}
