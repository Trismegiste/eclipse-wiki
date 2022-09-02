<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Voronoi\TileSetIterator;
use Symfony\Component\Finder\Finder;

/**
 * Repository for Tiles set
 */
class TileProvider
{

    protected $tilePath;

    public function __construct(string $tilePath)
    {
        $this->tilePath = $tilePath;
    }

    public function getTileSet(string $title): TileSetIterator
    {
        $finder = new Finder();
        $finder->in($this->tilePath . "/$title")
                ->files()
                ->name('*.svg');

        return new TileSetIterator($finder->getIterator());
    }

}
