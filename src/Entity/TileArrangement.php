<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use Trismegiste\Strangelove\MongoDb\Root;
use Trismegiste\Strangelove\MongoDb\RootImpl;

/**
 * This is a group of HexagonalTile
 */
class TileArrangement implements Root
{

    use RootImpl;

    protected $title;
    protected $collection;

    public function setTitle(string $str): void
    {
        $this->title = $str;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setCollection(array $collec): void
    {
        $this->collection = $collec;
    }

    public function getCollection(): array
    {
        return$this->collection;
    }

}
