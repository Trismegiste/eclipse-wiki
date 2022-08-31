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
        $tmp = preg_split('//u', $title, -1, PREG_SPLIT_NO_EMPTY);
        $firstLetter = array_shift($tmp);

        return $this->searchOne(['title' => new Regex("^(?i:$firstLetter)" . preg_quote(implode('', $tmp)) . '$')]);
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
        $tmp = preg_split('//u', $title, -1, PREG_SPLIT_NO_EMPTY);
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

    protected function getLastModified(string $pk): \MongoDB\BSON\UTCDateTime
    {
        $current = $this->manager->executeQuery($this->getNamespace(), new Query(
                    ['_id' => new ObjectId($pk)],
                    ['limit' => 1, 'projection' => ['lastModified' => true]]))
            ->toArray();

        if (0 == count($current)) {
            throw new \RuntimeException("The document with _id='$pk' was not found.");
        }

        return $current[0]->lastModified;
    }

    public function searchPreviousOf(string $pk): ?Vertex
    {
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query(
                ['lastModified' => ['$gt' => $this->getLastModified($pk)]],
                ['limit' => 1, 'sort' => ['lastModified' => 1]]));

        $item = $cursor->toArray();

        return count($item) ? $item[0] : null;
    }

    public function searchNextOf(string $pk): ?Vertex
    {
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query(
                ['lastModified' => ['$lt' => $this->getLastModified($pk)]],
                ['limit' => 1, 'sort' => ['lastModified' => -1]]));

        $item = $cursor->toArray();

        return count($item) ? $item[0] : null;
    }

    public function renameTitle(string $oldTitle, string $newTitle): int
    {
        $vertex = $this->findByTitle($oldTitle);
        // build the regex with insensitive case on the first letter
        $tmp = preg_split('//u', $vertex->getTitle(), -1, PREG_SPLIT_NO_EMPTY);
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

        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query($filter, [
                'sort' => [
                    'archived' => 1,
                    'lastModified' => -1
                ]
        ]));

        return new \IteratorIterator($cursor);
    }

    public function findByClass($fqcn, array $filter = []): \IteratorIterator
    {
        // managing parameters
        if (is_string($fqcn)) {
            $fqcn = [$fqcn];
        }
        if (!is_array($fqcn)) {
            throw new \InvalidArgumentException('Not an array');
        }

        // convert to MongoDb
        array_walk($fqcn, function (string &$val) {
            if (!class_exists($val)) {
                throw new \DomainException("FQCN $val does not exist");
            }
            $val = new Binary($val, Binary::TYPE_USER_DEFINED);
        });

        // returning query
        $filter['__pclass'] = ['$in' => $fqcn];
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query(
                $filter,
                ['sort' => ['title' => 1]]
        ));

        return new \IteratorIterator($cursor);
    }

    public function sortedExport(): \IteratorIterator
    {
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query(
                [],
                ['sort' => ['lastModified' => 1]]
        ));

        return new \IteratorIterator($cursor);
    }

}
