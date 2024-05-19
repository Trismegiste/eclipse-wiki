<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Ali;
use App\Entity\Cursor\AggregateCounter;
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
use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\Query;
use RuntimeException;
use Trismegiste\Strangelove\MongoDb\DefaultRepository;

/**
 * Repository for Vertices
 */
class VertexRepository extends DefaultRepository
{

    protected function getFirstLetterCaseInsensitiveRegexPart(string $title): string
    {
        return Vertex::getFirstLetterCaseInsensitiveRegexPart($title);
    }

    /**
     * Find the first vertex by its title. First letter is case-insensitive
     * @param string $title
     * @return Vertex
     */
    public function findByTitle(string $title): ?Vertex
    {
        return $this->searchOne(['title' => mb_ucfirst($title)]);
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
        $it = $this->search(['outboundLink' => mb_ucfirst($title)]);

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
        // search for vertex with links to $vertex
        $iter = $this->search(['outboundLink' => $vertex->getTitle()]);
        $updated = [];
        foreach ($iter as $inbound) {
            $inbound->renameInternalLink($vertex->getTitle(), $newTitle);
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
        if (!empty($keyword)) {
            $cursor = $this->manager->executeQuery($this->getNamespace(),
                    new Query(['$text' => ['$search' => $keyword]], [
                        'projection' => [
                            'content' => false,
                            'score' => ['$meta' => "textScore"]
                        ],
                        'sort' => [
                            'score' => ['$meta' => "textScore"]
                        ],
                        // we don't need to inject the score property in objects.
                        // Besides, it's deprecated to create dynamic property
                        'projection' => [
                            'score' => false
                        ],
                            ])
            );
        } else {
            $cursor = $this->manager->executeQuery($this->getNamespace(), new Query([], [
                        'sort' => [
                            'archived' => 1,
                            'lastModified' => -1
                        ]
            ]));
        }

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
                // optim : do not fetch vertex already fetched but we must continue exploring since the current distance for this path could be shorter than a previous path
                $item = key_exists($neighbour, $carry) ? $carry[$neighbour] : $this->findByTitle($neighbour);
                if (!is_null($item) && !($item instanceof Timeline)) {
                    $this->recursionExploreTimeline($item, $level - 1, $carry);
                }
            }
        }
    }

    /**
     * Returns a count, archived or not for each Vertex subclass
     * @return Cursor a cursor that iterates on AggregateCounter objects
     */
    public function countByClass(): Cursor
    {
        $cursor = $this->manager->executeReadCommand($this->dbName, new Command([
                    'aggregate' => $this->collectionName,
                    // the pipeline is an array of stages
                    'pipeline' => [
                        // first stage, grouping and counting :
                        [
                            '$group' => [
                                // primary key to group :
                                '_id' => ['key' => '$__pclass'],
                                // adds 1 for each document
                                'total' => ['$sum' => 1],
                                // adds 1 or 0 according to field 'archived'
                                'archived' => [
                                    '$sum' => [
                                        '$cond' => ['if' => '$archived', 'then' => 1, 'else' => 0]
                                    ]
                                ]
                            ]
                        ],
                        // second stage, projecting for removing noise
                        [
                            '$project' => ['fqcn' => '$_id.key', 'total' => true, 'archived' => true, '_id' => false]
                        ],
                        // third stage, sorting :
                        [
                            '$sort' => ['total' => -1]
                        ]
                    ],
                    'cursor' => ['batchSize' => 0]
        ]));
        $cursor->setTypeMap(['root' => AggregateCounter::class]);

        return $cursor;
    }

    /**
     * Gets all links in all vertices (existing or not)
     * @return Cursor
     */
    public function dumpAllInternalLinks(): Cursor
    {
        $cursor = $this->manager->executeReadCommand($this->dbName, new Command([
                    'aggregate' => $this->collectionName,
                    // the pipeline is an array of stages
                    'pipeline' => [
                        // unwind on all outbound links
                        ['$unwind' => '$outboundLink'],
                        // remove noise
                        ['$project' => ['title' => true, 'outboundLink' => true]],
                    ],
                    'cursor' => ['batchSize' => 0]
        ]));

        return $cursor;
    }

    /**
     * Calculates the adjacency matrix for the current digraph stored in vertices collection
     * @return array
     */
    public function getAdjacencyMatrix(): array
    {
        // census of vertices
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query([], ['projection' => ['_id' => true]]));
        $vertexPk = [];
        foreach ($cursor as $vertex) {
            $vertexPk[(string) $vertex->_id] = false;
        }

        // init matrix
        $matrix = [];
        foreach ($vertexPk as $pk => $dummy) {
            $matrix[$pk] = $vertexPk;
        }

        $cursor = $this->manager->executeReadCommand($this->dbName, new Command([
                    'aggregate' => $this->collectionName,
                    'cursor' => ['batchSize' => 0],
                    // the pipeline is an array of stages
                    'pipeline' => [
                        // unwind on all outbound links in the content
                        ['$unwind' => '$outboundLink'],
                        // left join in the same collection
                        [
                            '$lookup' => [
                                'from' => $this->collectionName,
                                'localField' => 'outboundLink',
                                'foreignField' => 'title',
                                'as' => 'internalLink'
                            ]
                        ],
                        // remove noise
                        ['$project' => ['title' => true, 'linkLabel' => '$outboundLink', 'outboundVertexId' => '$internalLink._id']],
                        // unwind on the id found
                        ['$unwind' => '$outboundVertexId']
                    ]
        ]));

        foreach ($cursor as $edge) {
            $matrix[(string) $edge->_id][(string) $edge->outboundVertexId] = true;
        }

        return $matrix;
    }

    public function searchAllTitleOnly(): Cursor
    {
        return $this->manager->executeQuery($this->getNamespace(), new Query([], [
                            'projection' => [
                                '_id' => true,
                                'title' => true,
                                '__pclass' => true
                            ]
        ]));
    }

}
