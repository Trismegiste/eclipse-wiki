<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use ArrayAccess;
use JsonSerializable;
use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * A node in the plot tree
 */
class PlotNode implements Persistable, JsonSerializable, ArrayAccess, \IteratorAggregate
{

    use PersistableImpl;

    public function __construct(
            public string $title,
            public array $nodes = [],
            public bool $finished = false)
    {
        
    }

    public function jsonSerialize(): mixed
    {
        $json = [
            'data' => ['title' => $this->title, 'finished' => $this->finished],
            'nodes' => $this->nodes
        ];

        return $json;
    }

    public function offsetExists(mixed $offset): bool
    {
        return key_exists($offset, $this->nodes);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->nodes[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->nodes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->nodes[$offset]);
    }

    public function getIterator(): \Traversable
    {
        return new \IteratorIterator($this->nodes);
    }

}
