<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\CreationTree;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * a creation node from the creation tree
 */
class Node implements Persistable
{

    use PersistableImpl;

    public string $name;
    public Modifier $bonus;
    public array $children;

}
