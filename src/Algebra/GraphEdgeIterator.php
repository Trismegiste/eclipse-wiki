<?php

/*
 * Eclipse Wiki
 */

namespace App\Algebra;

use Iterator;
use MongoDB\Driver\Cursor;

/**
 * Iterator on GraphEdge
 */
class GraphEdgeIterator implements Iterator
{

    public function __construct(protected Cursor $cursor)
    {
        $cursor->setTypeMap(['root' => 'array']);
    }

    public function current(): mixed
    {
        return new GraphEdge($this->cursor->current());
    }

    public function key(): mixed
    {
        return $this->cursor->key();
    }

    public function next(): void
    {
        $this->cursor->next();
    }

    public function rewind(): void
    {
        $this->cursor->rewind();
    }

    public function valid(): bool
    {
        return $this->cursor->valid();
    }

}
