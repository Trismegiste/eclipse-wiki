<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use JsonSerializable;
use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * An Edge
 */
class Edge extends Modifier implements Persistable, JsonSerializable
{

    use PersistableImpl;

    protected string $rank;
    protected string $category;
    protected $requis;
    public $origin = null; // creation, gift, xperience, morph, morph slot...

    public function __construct(string $str, string $rank, string $category, bool $ego = false, bool $biomorph = false, bool $synthmorph = false, $requis = '')
    {
        parent::__construct($str, $ego, $biomorph, $synthmorph);
        $this->rank = $rank;
        $this->category = $category;
        $this->requis = $requis;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getRank(): string
    {
        return $this->rank;
    }

    public function getPrerequisite(): string
    {
        return $this->requis;
    }

    public function jsonSerialize(): mixed
    {
        return $this->bsonSerialize();
    }

}
