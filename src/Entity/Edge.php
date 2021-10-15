<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Toolbox\MongoDb\PersistableImpl;

/**
 * An Edge
 */
class Edge implements Persistable
{

    use PersistableImpl;

    protected $name;
    protected $rank;
    protected $category;
    public $source = null;

    public function __construct(string $str, string $rank, string $category)
    {
        $this->name = $str;
        $this->rank = $rank;
        $this->category = $category;
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

}
