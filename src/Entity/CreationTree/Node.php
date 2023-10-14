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
    public array $attributes = [];
    public array $skills = [];
    public array $edges = [];
    public array $networks = [];
    public array $factions = [];
    public array $backgrounds = [];
    public array $morphs = [];
    public array $text2img = [];
    public array $children;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

}
