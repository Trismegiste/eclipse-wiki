<?php

/*
 * eclipse-wiki
 */

namespace App\Voronoi;

use Iterator;

/**
 * Decorator for Iterator on TileSvg
 */
class TileSetIterator implements Iterator
{

    protected Iterator $wrapped;

    public function __construct(Iterator $fileIterator)
    {
        $this->wrapped = $fileIterator;
    }

    public function current(): TileSvg
    {
        $svg = new TileSvg();
        $svg->load($this->wrapped->current()->getPathname());
        
        return $svg;
    }

    public function key(): string
    {
        return $this->wrapped->key();
    }

    public function next(): void
    {
        $this->wrapped->next();
    }

    public function rewind(): void
    {
        $this->wrapped->rewind();
    }

    public function valid(): bool
    {
        return $this->wrapped->valid();
    }

}
