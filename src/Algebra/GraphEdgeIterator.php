<?php

/*
 * Eclipse Wiki
 */

namespace App\Algebra;

use Iterator;

/**
 * Iterator on GraphEdge
 * DP : Decorator for an Iterator with a Factory
 */
class GraphEdgeIterator implements Iterator
{

    public function __construct(protected \Iterator $cursor)
    {
        
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
