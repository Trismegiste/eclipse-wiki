<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Place;
use App\Entity\Timeline;
use App\Entity\Vertex;
use App\Repository\VertexRepository;
use Collator;
use DateInterval;
use IteratorIterator;
use MongoDB\BSON\ObjectId;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Trismegiste\Strangelove\MongoDb\Repository;

/**
 * Some heavy operations on the digraph
 */
class DigraphExplore
{

    const INFINITY = 32767;

    protected VertexRepository $repository;
    protected CacheInterface $cache;
    protected string $localeParam;

    public function __construct(VertexRepository $repo, CacheInterface $cache, string $locale)
    {
        $this->repository = $repo;
        $this->cache = $cache;
        $this->localeParam = $locale;
    }

    /**
     * Gets the partition for a given Timeline
     * It returns an array of vertices close to the Timeline
     *  - CACHED -
     * @return array
     */
    public function graphToSortedCategory(Timeline $root): array
    {
        return $this->cache->get('tree_extract_' . $root->getPk(), function (ItemInterface $item) use ($root) {
                    $item->expiresAfter(DateInterval::createFromDateString('5 minute'));
                    $partition = $this->getPartitionByTimeline()[$root->getTitle()];
                    $intl = new Collator($this->localeParam);

                    $dump = [];
                    // dispatch by category
                    foreach ($partition as $v) {
                        $dump[$v->category][] = $v->title;
                    }
                    // sort each category
                    foreach ($dump as $key => $v) {
                        $intl->sort($dump[$key]);
                    }

                    return $dump;
                });
    }

    public function getPartitionByTimeline(): array
    {
        $graph = $this->repository->loadGraph();
        $dim = count($graph->vertex);

        // Initialize distance matrix
        $matrix = [];
        for ($row = 0; $row < $dim; $row++) {
            $matrix[$row] = [];
            for ($col = 0; $col < $dim; $col++) {
                $matrix[$row][$col] = match (true) {
                    $row === $col => 0,
                    $graph->adjacency[$row][$col] || $graph->adjacency[$col][$row] => 1,
                    default => self::INFINITY
                };
            }
        }

        // Floyd-Warshall algorithm
        // https://en.wikipedia.org/wiki/Floyd%E2%80%93Warshall_algorithm
        for ($k = 0; $k < $dim; $k++) {
            for ($line = 0; $line < $dim; $line++) {
                for ($column = 0; $column < $dim; $column++) {
                    $newSum = $matrix[$line][$k] + $matrix[$k][$column];
                    if ($newSum < $matrix[$line][$column]) {
                        $matrix[$line][$column] = $newSum;
                    }
                }
            }
        }

        // initialize partitions
        $partition = [];
        $timeline = $graph->extractVectorByCategory('timeline');
        foreach ($timeline as $vertex) {
            $partition[$vertex->title] = [];
        }

        // group vertices by closest Timeline. If a vertex is closest to multiple Timeline, it is duplicated
        foreach ($matrix as $row => $column) {
            // if the vertex is a Timeline, skip
            if (key_exists($row, $timeline)) {
                continue;
            }
            // extract all distances from this vertex to all Timeline
            $distanceToTimeline = [];
            foreach ($timeline as $origin => $notused) {
                $distanceToTimeline[$origin] = $column[$origin];
            }
            // sort extracted Timeline by distance
            asort($distanceToTimeline);
            // get the closest distance
            $found = array_key_first($distanceToTimeline);
            $minDist = $distanceToTimeline[$found];
            // if it's infinity, it's an orphan, skip
            if ($minDist === self::INFINITY) {
                continue;
            }
            // append the vertex to all Timeline at the same closest distance
            foreach ($distanceToTimeline as $found => $dist) {
                if ($minDist === $dist) {
                    $vertex = clone $graph->getVertexByIndex($row);
                    $vertex->distance = $dist;
                    $partition[$timeline[$found]->title][] = $vertex;
                }
            }
        }

        return $partition;
    }

    /**
     * This algorithm creates a partition of vertices in the wiki graph
     * For each vertex, it calculates the shortest distance to all Timeline
     * Then it regroups all vertices to the closest Timeline (or multiple Timeline if the vertex is equidistant to several Timeline)
     *  - SLOW -
     * @return array
     */
    public function getPartitionByTimelineV1(): array
    {
        $edge = $this->repository->getAdjacencyMatrix();
        $dim = count($edge);
        $assocId = array_keys($edge); // index => mongodb pk
        $invertAssocId = array_flip($assocId); // mongodb pk => index
        // Initialize distance matrix
        $matrix = [];
        for ($row = 0; $row < $dim; $row++) {
            $matrix[$row] = [];
            for ($col = 0; $col < $dim; $col++) {
                $matrix[$row][$col] = match (true) {
                    $row === $col => 0,
                    $edge[$assocId[$row]][$assocId[$col]] || $edge[$assocId[$col]][$assocId[$row]] => 1,
                    default => self::INFINITY
                };
            }
        }

        // Floyd-Warshall algorithm
        // https://en.wikipedia.org/wiki/Floyd%E2%80%93Warshall_algorithm
        for ($k = 0; $k < $dim; $k++) {
            for ($line = 0; $line < $dim; $line++) {
                for ($column = 0; $column < $dim; $column++) {
                    $newSum = $matrix[$line][$k] + $matrix[$k][$column];
                    if ($newSum < $matrix[$line][$column]) {
                        $matrix[$line][$column] = $newSum;
                    }
                }
            }
        }

        // dump minimal info for vertices and initialize partitions for each Timeline
        $timelineIdx = [];
        $title = [];
        $category = [];
        $partition = [];
        foreach ($this->repository->search() as $vertex) {
            $pk = (string) $vertex->getPk();
            $title[$pk] = $vertex->getTitle();
            $category[$pk] = $vertex->getCategory();
            if ($vertex instanceof \App\Entity\Timeline) {
                // keep the pk of Timeline for skipping in the next loop
                $timelineIdx[] = $invertAssocId[$pk];
                // initialise the partition for each Timeline
                $partition[$vertex->getTitle()] = [];
            }
        }

        // group vertices by closest Timeline. If a vertex is closest to multiple Timeline, it is duplicated
        foreach ($matrix as $row => $column) {
            // if the vertex is a Timeline, skip
            if (in_array($row, $timelineIdx)) {
                continue;
            }
            // extract all distances from this vertex to all Timeline
            $distanceToTimeline = [];
            foreach ($timelineIdx as $origin) {
                $distanceToTimeline[$origin] = $column[$origin];
            }
            // sort extracted Timeline by distance
            asort($distanceToTimeline);
            // get the closest distance
            $found = array_key_first($distanceToTimeline);
            $minDist = $distanceToTimeline[$found];
            // if it's infinity, it's an orphan, skip
            if ($minDist === self::INFINITY) {
                continue;
            }
            // append the vertex to all Timeline at the same closest distance
            foreach ($distanceToTimeline as $found => $dist) {
                if ($minDist === $dist) {
                    $partition[$title[$assocId[$found]]][] = [
                        'pk' => $assocId[$row],
                        'title' => $title[$assocId[$row]],
                        'category' => $category[$assocId[$row]],
                        'distance' => $dist
                    ];
                }
            }
        }

        return $partition;
    }

    /**
     * Searches for all orphan vertices
     * @return array
     */
    public function findOrphan(): array
    {
        $orphan = [];
        $matrix = $this->getAdjacencyMatrix();

        foreach ($matrix as $source => $row) {
            $isOrphan = true;
            // check outbound vertices
            foreach ($row as $target => $link) {
                if ($link && ($target !== $source)) {
                    $isOrphan = false;
                    break;
                }
            }
            // check inbound vertices
            foreach ($matrix as $inbound => $notused) {
                if (($inbound !== $source) && $matrix[$inbound][$source]) {
                    $isOrphan = false;
                    break;
                }
            }
            if ($isOrphan) {
                $orphan[] = $source;
            }
        }

        return $orphan;
    }

    /**
     * Calculates the adjacency matrix for the current digraph stored in vertices collection
     * @return array
     */
    public function getAdjacencyMatrix(): array
    {
        return $this->repository->getAdjacencyMatrix();
    }

    /**
     * Search for broken links in vertex content
     */
    public function searchForBrokenLink(): array
    {
        $keep = [];
        // @todo absolutely NOT optimized algorithm
        foreach ($this->repository->search(descendingSortField: 'lastModified') as $vertex) {
            $scan = $vertex->getInternalLink();
            foreach ($scan as $target) {
                if (is_null($this->repository->findByTitle($target))) {
                    $keep[$target][$vertex->getTitle()] = true;
                }
            }
        }

        return $keep;
    }

    /**
     * Searches for broken links in vertex content. This list is filtered for one Timeline
     * Vertices are connected to one Timeline thanks to the Floyd-Warshall algorithm
     *  - SLOW -
     */
    public function searchForBrokenLinkByTimeline(Timeline $root): array
    {
        $outbound = [];
        $edges = $this->repository->dumpAllInternalLinks();
        foreach ($edges as $link) {
            if (key_exists($link->title, $outbound)) {
                $outbound[$link->title][] = $link->outboundLink;
            } else {
                $outbound[$link->title] = [$link->outboundLink];
            }
        }

        $partition = $this->getPartitionByTimeline();
        $keep = [];
        foreach ($partition[$root->getTitle()] as $vertex) {
            $title = $vertex->title;
            if (!key_exists($title, $outbound)) {
                continue;
            }
            foreach ($outbound[$title] as $target) {
                if (is_null($this->repository->findByTitle($target))) {
                    $keep[$target][$title] = true;
                }
            }
        }

        return $keep;
    }

    /**
     * Search of connected places (inbound/outbound) for a given Place
     * @param Place $place
     * @return IteratorIterator
     */
    public function searchForConnectedPlace(Place $place): IteratorIterator
    {
        $pk = (string) $place->getPk();
        $matrix = $this->repository->getAdjacencyMatrix();
        $edges = [];

        foreach ($matrix[$pk] as $target => $flag) {
            if ($flag) {
                $edges[] = new ObjectId($target);
            }
        }

        foreach ($matrix as $source => $row) {
            if ($row[$pk]) {
                $edges[] = new ObjectId($source);
            }
        }

        return $this->repository->findByClass(Place::class, ['_id' => ['$in' => $edges]]);
    }

    public function getNonDirectedGraphAdjacency(): array
    {
        $listing = [];
        $cursor = $this->repository->searchAllTitleOnly();
        $cursor->setTypeMap(['root' => 'array']);
        foreach ($cursor as $vertex) {
            $pk = (string) $vertex['_id'];
            $listing[] = [
                'id' => $pk,
                'title' => $vertex['title'],
                'class' => Vertex::getCategoryForVertex($vertex['__pclass'])
            ];
        }

        $adjacency = $this->repository->getAdjacencyMatrix();
        $matrix = [];
        foreach ($listing as $rowIdx => $rowVertex) {
            foreach ($listing as $colIdx => $colVertex) {
                $rowPk = $rowVertex['id'];
                $colPk = $colVertex['id'];
                $matrix[$rowIdx][$colIdx] = $adjacency[$rowPk][$colPk] || $adjacency[$colPk][$rowPk];
            }
        }

        return [
            'vertex' => $listing,
            'adjacency' => $matrix
        ];
    }

}
