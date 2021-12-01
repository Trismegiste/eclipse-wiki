<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Vertex;
use MongoDB\BSON\Regex;
use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * Repository for Vertices
 */
class VertexRepository
{

    protected $collection;

    public function __construct(Repository $vertexRepo)
    {
        $this->collection = $vertexRepo;
    }

    /**
     * Find the first vertex by its title. First letter is case-insensitive
     * @param string $title
     * @return type
     */
    public function findByTitle(string $title): ?Vertex
    {
        $tmp = preg_split('//u', $title, null, PREG_SPLIT_NO_EMPTY);
        $firstLetter = array_shift($tmp);

        $it = $this->collection->search([
            'title' => new Regex("(?i:$firstLetter)" . implode('', $tmp))
        ]);
        $it->rewind();

        return $it->current();
    }

    /**
     * Gets an iterator against all vertices collection
     * @return \IteratorIterator
     */
    public function findAll(): \IteratorIterator
    {
        return $this->collection->search([], [], '_id');
    }

    /**
     * Gets the vertex by its primary key
     * @param string $pk
     * @return Vertex
     */
    public function findByPk(string $pk): Vertex
    {
        return $this->collection->load($pk);
    }

    public function save(Vertex $v): void
    {
        $this->collection->save($v);
    }

    public function delete(Vertex $obj): void
    {
        $this->collection->delete($obj);
    }

    public function searchStartingWith(string $title): array
    {
        return $this->collection->searchAutocomplete('title', $title);
    }

    public function searchByBacklinks(string $title): array
    {
        $tmp = preg_split('//u', $title, null, PREG_SPLIT_NO_EMPTY);
        $firstLetter = array_shift($tmp);

        $it = $this->collection->search([
            'content' => new Regex("\[\[(?i:$firstLetter)" . implode('', $tmp) . "(\]\]|\|)")
        ]);

        $linked = [];
        foreach ($it as $vertex) {
            $linked[] = $vertex->getTitle();
        }

        return $linked;
    }

}
