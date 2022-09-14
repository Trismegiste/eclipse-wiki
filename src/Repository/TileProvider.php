<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Voronoi\TileSetIterator;
use App\Voronoi\TileSvg;
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

    /**
     * Gets all tiles from a tileset
     * @param string $title the tileset name
     * @return TileSetIterator
     */
    public function getTileSet(string $title): TileSetIterator
    {
        $finder = new Finder();
        $finder->in($this->tilePath . "/$title")
                ->files()
                ->name('*.svg');

        return new TileSetIterator($finder->getIterator());
    }

    /**
     * Gets clusters tiles from a tileset
     * @param string $title the tileset name
     * @return TileSetIterator
     */
    public function getClusterSet(string $title): TileSetIterator
    {
        $finder = new Finder();
        $finder->in($this->tilePath . "/$title")
                ->files()
                ->name('cluster-*.svg');

        return new TileSetIterator($finder->getIterator());
    }

    /**
     * Gets one tile
     * @param string $tileSet the tileset name
     * @param string $tileKey the tile key in the tileset
     * @return TileSvg
     */
    public function findByKey(string $tileSet, string $tileKey): TileSvg
    {
        $tile = new TileSvg();
        $tile->load($this->tilePath . "/$tileSet/$tileKey.svg");

        return $tile;
    }

}
