<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * Morph for a NPC
 */
class Morph implements Indexable, Persistable
{

    use PersistableImpl,
        EdgeContainer;

    public string $title;
    public array $ability = [];
    public array $disability = [];
    public $type;
    public $price;
    public array $skillBonus = [];
    public array $attributeBonus = [];

    public function __construct(string $param)
    {
        $this->title = $param;
    }

    public function getUId(): string
    {
        return $this->title;
    }

}
