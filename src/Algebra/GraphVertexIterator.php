<?php

/*
 * Eclipse Wiki
 */

namespace App\Algebra;

use MongoDB\Driver\Cursor;

/**
 * Iterator on GraphVertex
 */
class GraphVertexIterator implements \Iterator
{

    public function __construct(protected Cursor $cursor)
    {
        $cursor->setTypeMap(['root' => 'array']);
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
