<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Algebra\Digraph;
use App\Algebra\GraphEdgeIterator;
use App\Algebra\GraphVertexIterator;
use App\Entity\Ali;
use App\Entity\Cursor\AggregateCounter;
use App\Entity\Freeform;
use App\Entity\Subgraph;
use App\Entity\Timeline;
use App\Entity\Transhuman;
use App\Entity\Vertex;
use DomainException;
use InvalidArgumentException;
use Iterator;
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
use function mb_ucfirst;

/**
 * Repository for Vertices
 */
class VertexRepository extends DefaultRepository
{

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

    /**
     * Generic search for most-used listing of vertices
     * @param string $keyword
     * @return IteratorIterator
     */
    public function filterBy(string $keyword): IteratorIterator
    {
        $this->logger->debug('Full-text search for keyword=' . $keyword);
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
     * Regex search a keyword in the content of all Vertex collection but we exclude vertices if the keyword in a wikilink or title.
     * Useful for tracking mentions of a Vertex before renaming it.
     * @param string $keyword
     * @return IteratorIterator
     */
    public function searchKeywordNotLink(string $keyword): IteratorIterator
    {
        $cursor = $this->manager->executeQuery($this->getNamespace(),
                new Query([
                    'content' => new Regex($keyword, 'i'),
                    'outboundLink' => ['$ne' => $keyword],
                    'title' => ['$ne' => $keyword]
                        ])
        );

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
     * Searches all Timeline
     * @return iterable
     */
    public function searchTimeline(): iterable
    {
        return $this->manager->executeQuery($this->getNamespace(), new Query(
                                ['__pclass' => new Binary(Timeline::class, Binary::TYPE_USER_DEFINED)],
                                ['sort' => [
                                'archived' => 1,
                                'lastModified' => -1
                            ]]
        ));
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
     * Gets an iterator on all existing vertices. Each value is a GraphVertex and its key is its MongoDb primary key
     * @return Iterator
     */
    public function searchGraphVertex(array $filter = []): Iterator
    {
        $cursor = $this->manager->executeQuery($this->getNamespace(), new Query($filter, [
                    'projection' => [
                        '_id' => true,
                        'title' => true,
                        '__pclass' => true
                    ]
        ]));
        $cursor->setTypeMap(['root' => 'array']);

        return new GraphVertexIterator($cursor);
    }

    /**
     * Gets an iterator on all existing edges. Each value is a GraphEdge
     * @return Iterator
     */
    public function searchGraphEdge(): Iterator
    {
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
                        ['$project' => ['source' => '$_id', 'target' => '$internalLink._id']],
                        // unwind on the id found
                        ['$unwind' => '$target']
                    ]
        ]));
        $cursor->setTypeMap(['root' => 'array']);

        return new GraphEdgeIterator($cursor);
    }

    /**
     * Loads the full digraph
     * @return Digraph
     */
    public function loadGraph(): Digraph
    {
        $graph = new Digraph($this->searchGraphVertex());
        $graph->setAdjacency($this->searchGraphEdge());

        return $graph;
    }

    /**
     * Calculates the adjacency matrix for the current digraph stored in vertices collection
     * @deprecated
     * @return array
     */
    public function getAdjacencyMatrix(): array
    {
        $graph = $this->loadGraph();
        $retro = [];
        $dim = count($graph->vertex);
        for ($row = 0; $row < $dim; $row++) {
            for ($col = 0; $col < $dim; $col++) {
                $rowPk = $graph->getVertexByIndex($row)->pk;
                $colPk = $graph->getVertexByIndex($col)->pk;
                $retro[$rowPk][$colPk] = $graph->adjacency[$row][$col];
            }
        }

        return $retro;
    }

    /**
     * Searches all bakclinks for a given Vertex
     * @param Vertex $vertex
     * @return IteratorIterator
     */
    public function searchInbound(Vertex $vertex): IteratorIterator
    {
        $this->logger->debug('Loading inbound vertices for title=' . $vertex->getTitle());
        return $this->search(['outboundLink' => $vertex->getTitle()]);
    }

    /**
     * Loads a subgraph including one vertex (the focus) and its inbound vertices
     * @param string $pk
     * @return Subgraph
     */
    public function loadSubgraph(string $pk): Subgraph
    {
        $this->logger->debug('Loading subgraph for pk=' . $pk);
        $focus = $this->load($pk);
        $subgraph = new Subgraph($focus);
        foreach ($this->searchInbound($focus)as $inbound) {
            $subgraph->appendInbound($inbound);
        }

        return $subgraph;
    }

    /**
     * Returns an array with keys as titles and values as found pks (or null if not found)
     */
    public function searchPkByTitle(array $title): array
    {
        $this->logger->debug('Searching pk for title=' . json_encode($title));

        $matched = array_combine($title, array_fill(0, count($title), null));
        $iter = $this->searchGraphVertex(['title' => ['$in' => array_map('mb_ucfirst', $title)]]); // we use GraphVertex to project only on useful columns
        $found = array_column(iterator_to_array($iter), 'pk', 'title'); // we flatten the cursor and extract an array [title => pk]

        // This algo seems inefficient but it's the only way to keep the character-case of the requested titles,
        // including the wicked use-case when the same vertex appears 2 times in the list with
        // 2 different spellings (the first character of its title is uppercase or not).
        // Therefore, we must iterate on the $title array and check on the database content (previously flattened, see above).
        // Iterating on the database content (see $iter above) and updating the $matched array will be
        // too ugly (double checking, case insensitive regex... yikes)
        foreach ($title as $entry) {
            $searched = mb_ucfirst($entry);
            if (key_exists($searched, $found)) {
                $matched[$entry] = $found[$searched];
            }
        }

        return $matched;
    }

    public function findSubsetSortedByTitle(array $pk): iterable
    {
        return $this->manager->executeQuery($this->getNamespace(),
                        new Query(['_id' => ['$in' => $pk]],
                                ['collation' => ['locale' => 'fr'], 'sort' => ['title' => 1]]));
    }

}
