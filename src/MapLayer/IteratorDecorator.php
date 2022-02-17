<?php

/*
 * eclipse-wiki
 */

namespace App\MapLayer;

/**
 * Decorates info from Map recipe iterator
 */
class IteratorDecorator implements \Iterator
{

    protected $iter;

    /**
     * Ctor
     * @param Iterator $iter any Iterator
     * @param callable $strat a callable that will be called with one argument : Iterator::current()
     */
    public function __construct(\Iterator $iter)
    {
        $this->iter = $iter;
    }

    /**
     * Returns the current element of the iterator decorated by the callable
     * @return mixed
     */
    public function current()
    {
        /** @var \Symfony\Component\Finder\SplFileInfo $info */
        $info = $this->iter->current();

        return new ThumbnailMap($info);
    }

    // next methods are just redirections
    public function key()
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

}
