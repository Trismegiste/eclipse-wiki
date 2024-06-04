<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Algebra\GraphVertex;
use App\Entity\Place;
use App\Entity\Timeline;
use App\Repository\VertexRepository;
use Collator;
use DateInterval;
use IteratorIterator;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Some heavy operations on the digraph
 */
class DigraphExplore
{

    const INFINITY = 32767;

    protected VertexRepository $repository;
    protected CacheInterface $cache;
    protected string $localeParam;

    public function __construct(VertexRepository $repo,
            CacheInterface $cache,
            string $locale,
            protected \Symfony\Contracts\HttpClient\HttpClientInterface $client,
            protected \Symfony\Component\Stopwatch\Stopwatch $stopwatch)
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

    /**
     * This algorithm creates a partition of vertices in the wiki graph
     * For each vertex, it calculates the shortest distance to all Timeline
     * Then it regroups all vertices to the closest Timeline (or multiple Timeline if the vertex is equidistant to several Timeline)
     *  - SLOW -
     * @return array
     */
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

        $this->stopwatch->start('floyd-warshall');
        if (true) {
            $response = $this->client->request('POST', 'http://localhost:3333/algebra/floydwarshall', [
                'json' => $matrix
            ]);
            $matrix = json_decode($response->getContent(), true);
        } else {
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
        }
        $this->stopwatch->stop('floyd-warshall');

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
     * Searches for all orphan vertices
     * @return array
     */
    public function findOrphan(): array
    {
        return $this->repository->loadGraph()->searchOrphan();
    }

    /**
     * Calculates the adjacency matrix for the current digraph stored in vertices collection
     * @return array
     * @deprecated
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
    public function searchForConnectedPlace(Place $place): array
    {
        $graph = $this->repository->loadGraph();

        return array_filter($graph->getConnectedVertex($place->getPk()), function (GraphVertex $v) {
            return $v->category === 'place';
        });
    }

    public function getNonDirectedGraphAdjacency(): array
    {
        $graph = $this->repository->loadGraph();

        return [
            'vertex' => array_values($graph->vertex),
            'adjacency' => $graph->createUndirectedAdjacency()
        ];
    }

}
