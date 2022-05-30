<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Vertex;
use MongoDB\BSON\Binary;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\Driver\Query;
use Trismegiste\Strangelove\MongoDb\DefaultRepository;

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

        return $this->searchOne(['title' => new Regex("^(?i:$firstLetter)" . preg_quote(implode('', $tmp)))]);
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
            'content' => new Regex("\[\[(?i:$firstLetter)" . preg_quote(implode('', $tmp)) . "(\]\]|\|)")
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
                        ['_id' => ['$gt' => new ObjectId($pk)]],
                        ['limit' => 1, 'sort' => ['_id' => 1]]));

        $item = $cursor->toArray();

        return count($item) ? $item[0] : null;
    }

    public function searchNextOf(string $pk): ?Vertex
    {
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query(
                        ['_id' => ['$lt' => new ObjectId($pk)]],
                        ['limit' => 1, 'sort' => ['_id' => -1]]));

        $item = $cursor->toArray();

        return count($item) ? $item[0] : null;
    }

    public function renameTitle(string $oldTitle, string $newTitle): int
    {
        $vertex = $this->findByTitle($oldTitle);
        // build the regex with insensitive case on the first letter
        $tmp = preg_split('//u', $vertex->getTitle(), null, PREG_SPLIT_NO_EMPTY);
        $firstLetter = array_shift($tmp);
        $regex = "\[\[(?i:$firstLetter)" . preg_quote(implode('', $tmp)) . "(\]\]|\|)";

        // search for vertex with links to $vertex
        $iter = $this->search(['content' => new Regex($regex)]);
        $updated = [];
        foreach ($iter as $inbound) {
            $content = $inbound->getContent();
            $replacing = preg_replace('#' . $regex . '#', "[[$newTitle" . '$1', $content);
            $inbound->setContent($replacing);
            $updated[] = $inbound;
        }
        $vertex->setTitle($newTitle);
        $updated[] = $vertex;

        $this->save($updated);

        return count($updated);
    }

    public function filterBy(string $keyword): \IteratorIterator
    {
        $filter = [];
        if (!empty($keyword)) {
            $filter = ['$or' => [
                    ['title' => new Regex(preg_quote($keyword), 'i')],
                    ['content' => new Regex(preg_quote($keyword), 'i')]
            ]];
        }

        $it = $this->search($filter, [], '_id');

        return $it;
    }

    public function findByClass($fqcn): \IteratorIterator
    {
        // managing parameters
        if (is_string($fqcn)) {
            $fqcn = [$fqcn];
        }
        if (!is_array($fqcn)) {
            throw new \InvalidArgumentException('Not an array');
        }

        // convert to MongoDb
        array_walk($fqcn, function (&$val) {
            $val = new Binary($val, Binary::TYPE_USER_DEFINED);
        });

        // returning query
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query(
                        ['__pclass' => ['$in' => $fqcn]],
                        ['sort' => ['title' => 1]]
        ));

        return new \IteratorIterator($cursor);
    }

}
