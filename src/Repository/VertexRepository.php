<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Ali;
use App\Entity\Freeform;
use App\Entity\Timeline;
use App\Entity\Transhuman;
use App\Entity\Vertex;
use DomainException;
use InvalidArgumentException;
use IteratorIterator;
use MongoDB\BSON\Binary;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Query;
use RuntimeException;
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
     * @return IteratorIterator
     */
    public function findAll(): IteratorIterator
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

    protected function getLastModified(string $pk): UTCDateTime
    {
        $current = $this->manager->executeQuery($this->getNamespace(), new Query(
                                ['_id' => new ObjectId($pk)],
                                ['limit' => 1, 'projection' => ['lastModified' => true]]))
                ->toArray();

        if (0 == count($current)) {
            throw new RuntimeException("The document with _id='$pk' was not found.");
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

    /**
     * Generic search for most-used listing of vertices
     * @param string $keyword
     * @return IteratorIterator
     */
    public function filterBy(string $keyword): IteratorIterator
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

        return new IteratorIterator($cursor);
    }

    /**
     * Searches documents by FQCN
     * @param string|array $fqcn
     * @param array $filter
     * @return IteratorIterator
     * @throws InvalidArgumentException
     */
    public function findByClass(string|array $fqcn, array $filter = []): IteratorIterator
    {
        // managing parameters
        if (is_string($fqcn)) {
            $fqcn = [$fqcn];
        }

        // convert to MongoDb
        array_walk($fqcn, function (string &$val) {
            if (!class_exists($val)) {
                throw new DomainException("FQCN $val does not exist");
            }
            $val = new Binary($val, Binary::TYPE_USER_DEFINED);
        });

        // returning query
        $filter['__pclass'] = ['$in' => $fqcn];
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query(
                        $filter,
                        ['sort' => ['title' => 1]]
        ));

        return new IteratorIterator($cursor);
    }

    /**
     * Query for Database Export
     * @return IteratorIterator
     */
    public function sortedExport(): IteratorIterator
    {
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query(
                        [],
                        ['sort' => ['lastModified' => 1]]
        ));

        return new IteratorIterator($cursor);
    }

    /**
     * Searches all NPC with a token picture
     * @return IteratorIterator
     */
    public function searchNpcWithToken(): IteratorIterator
    {
        return $this->findByClass(
                        [
                            Ali::class,
                            Transhuman::class,
                            Freeform::class
                        ],
                        ['tokenPic' => ['$ne' => null]]
        );
    }

    /**
     * Starts from an instance of Timeline and recursively explores all adjacent neighbours (inbound and outbound)
     * until it reaches another Timeline instance or it crosses a given number of edges.
     * Imagine the whole digraph is partitioned into multiple trees (where roots are Timeline) and 
     * some branches are connected between some trees.
     * @param Timeline $vertex the starting point
     * @param int $level how many edges before stopping exploration of the digraph
     * @return array an array of Vertex of all close neighbours (unordered)
     */
    public function exploreTreeFrom(Timeline $vertex, int $level = 2): array
    {
        $carry = [];
        $this->recursionExploreTimeline($vertex, $level, $carry);

        return $carry;
    }

    // the recursion for the above method
    private function recursionExploreTimeline(Vertex $vertex, int $level, array &$carry): void
    {
        $title = $vertex->getTitle();
        $carry[$title] = $vertex;

        if ($level > 0) {
            $neighbours = array_unique(array_merge($vertex->getInternalLink(), $this->searchByBacklinks($title)));
            foreach ($neighbours as $neighbour) {
                // @todo can we skip the neighbour already fetched and stored in $carry ? I think so
                $item = $this->findByTitle($neighbour);
                if (!is_null($item) && !($item instanceof Timeline)) {
                    $this->recursionExploreTimeline($item, $level - 1, $carry);
                }
            }
        }
    }

}
