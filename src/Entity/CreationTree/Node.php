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
    public array $children = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    protected function beforeSave(): void
    {
        $this->edges = array_values($this->edges);
        $this->backgrounds = array_values($this->backgrounds);
        $this->factions = array_values($this->factions);
        $this->morphs = array_values($this->morphs);
        $this->children = array_values($this->children);
    }

}
