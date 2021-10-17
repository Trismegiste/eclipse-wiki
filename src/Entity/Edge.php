<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use JsonSerializable;
use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * An Edge
 */
class Edge implements Persistable, Indexable, JsonSerializable
{

    use PersistableImpl;

    protected $name;
    protected $rank;
    protected $category;
    protected $ego;
    protected $biomorph;
    protected $synthmorph;
    protected $requis;
    public $origin = null; // creation, gift, xperience, morph, morph slot...

    public function __construct(string $str, string $rank, string $category, $ego = false, $biomorph = false, $synthmorph = false, $requis = '')
    {
        $this->name = $str;
        $this->rank = $rank;
        $this->category = $category;
        $this->ego = $ego;
        $this->biomorph = $biomorph;
        $this->synthmorph = $synthmorph;
        $this->requis = $requis;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getRank(): string
    {
        return $this->rank;
    }

    public function getUId(): string
    {
        return $this->name;
    }

    public function getPrerequisite(): string
    {
        return $this->requis;
    }

    public function isBio(): bool
    {
        return $this->biomorph;
    }

    public function jsonSerialize()
    {
        return $this->bsonSerialize();
    }

    public function isSynth(): bool
    {
        return $this->synthmorph;
    }

    public function isEgo(): bool
    {
        return $this->ego;
    }

}
