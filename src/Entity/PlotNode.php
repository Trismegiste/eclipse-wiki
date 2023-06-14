<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

use MongoDB\BSON\Persistable;
use Trismegiste\Strangelove\MongoDb\PersistableImpl;

/**
 * A node in the plot tree
 */
class PlotNode implements Persistable, \JsonSerializable
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

}
