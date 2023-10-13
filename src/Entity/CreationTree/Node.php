<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\CreationTree;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;
use App\Entity\CreationTree\Modifier;

/**
 * a creation node from the creation tree
 */
class Node implements Persistable
{

    use PersistableImpl;

    public string $name;
    public Modifier $bonus;
    public array $text2img = [];
    public array $children;

    public function __construct(string $name, Modifier $bonus)
    {
        $this->name = $name;
        $this->bonus = $bonus;
    }

}
