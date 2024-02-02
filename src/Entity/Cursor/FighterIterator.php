<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Cursor;

use App\Entity\FighterJsonDecorator;
use Iterator;
use JsonSerializable;

/**
 * JSON-serialisable iterator on Fighter
 */
class FighterIterator implements Iterator, JsonSerializable
{

    public function __construct(protected Iterator $iter)
    {
        
    }

    public function current(): mixed
    {
        return new FighterJsonDecorator($this->iter->current());
    }

    public function key(): mixed
    {
        return $this->iter->key();
    }

    public function next(): void
    {
        $this->iter->next();
    }

    public function rewind(): void
    {
        $this->iter->rewind();
    }

    public function valid(): bool
    {
        return $this->iter->valid();
    }

    public function jsonSerialize(): mixed
    {
        return iterator_to_array($this);
    }

}
