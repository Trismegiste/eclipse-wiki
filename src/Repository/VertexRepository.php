<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Vertex;
use MongoDB\BSON\Regex;
use MongoDB\Driver\Query;
use Trismegiste\Toolbox\MongoDb\DefaultRepository;

/**
 * Repository for Vertices
 */
class VertexRepository extends DefaultRepository
{

    /**
     * Find the first vertex by its title. First letter is case-insensitive
     * @param string $title
     * @return type
     */
    public function findByTitle(string $title): ?Vertex
    {
        $tmp = preg_split('//u', $title, null, PREG_SPLIT_NO_EMPTY);
        $firstLetter = array_shift($tmp);

        $it = $this->search([
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
        return $this->search([], [], '_id');
    }

    /**
     * Gets the vertex by its primary key
     * @param string $pk
     * @return Vertex
     */
    public function findByPk(string $pk): Vertex
    {
        return $this->load($pk);
    }

    public function searchStartingWith(string $title): array
    {
        return $this->searchAutocomplete('title', $title);
    }

    public function searchByBacklinks(string $title): array
    {
        $tmp = preg_split('//u', $title, null, PREG_SPLIT_NO_EMPTY);
        $firstLetter = array_shift($tmp);

        $it = $this->search([
            'content' => new Regex("\[\[(?i:$firstLetter)" . implode('', $tmp) . "(\]\]|\|)")
        ]);

        $linked = [];
        foreach ($it as $vertex) {
            $linked[] = $vertex->getTitle();
        }

        return $linked;
    }

    public function searchPreviousOf(string $pk): ?Vertex
    {
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query(
                        ['_id' => ['$gt' => new \MongoDB\BSON\ObjectId($pk)]],
                        ['limit' => 1, 'sort' => ['_id' => 1]]));

        $cursor->rewind();

        return $cursor->current();
    }

    public function searchNextOf(string $pk): ?Vertex
    {
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query(
                        ['_id' => ['$lt' => new \MongoDB\BSON\ObjectId($pk)]],
                        ['limit' => 1, 'sort' => ['_id' => -1]]));

        $cursor->rewind();

        return $cursor->current();
    }

}
