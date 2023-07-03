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
    public int $bodyArmor = 0;

    public function __construct(string $param)
    {
        $this->title = $param;
    }

    public function getUId(): string
    {
        return $this->title;
    }

    public function searchAttributeBonus(string $abbrev): ?TraitBonus
    {
        return key_exists($abbrev, $this->attributeBonus) ? $this->attributeBonus[$abbrev] : null;
    }

    public function searchSkillBonus(string $name): ?TraitBonus
    {
        return key_exists($name, $this->skillBonus) ? $this->skillBonus[$name] : null;
    }

}
