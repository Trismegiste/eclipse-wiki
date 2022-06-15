<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use Symfony\Component\Finder\Finder;

/**
 * Repository of Tile
 */
class TileProvider
{

    protected $source;

    public function __construct(string $sourceDir)
    {
        $this->source = $sourceDir;
    }

    public function findAll(): array
    {
        $listing = new Finder();
        $listing->in($this->source)
                ->name('*.svg');

        return iterator_to_array($listing);
    }

}
