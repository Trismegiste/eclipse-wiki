<?php

/*
 * Eclipse Wiki
 */

namespace App\Algebra;

use Iterator;

/**
 * Iterator on GraphVertex
  * DP : Decorator for an Iterator with a Factory
 */
class GraphVertexIterator implements Iterator
{

    public function __construct(protected Iterator $cursor)
    {
        
    }

    public function current(): mixed
    {
        return new GraphVertex($this->cursor->current());
    }

    public function key(): mixed
    {
        return (string) $this->cursor->current()['_id'];
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
