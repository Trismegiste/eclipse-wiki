<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Algebra\Digraph;
use App\Algebra\GraphVertex;
use App\Entity\Place;
use App\Entity\Timeline;
use App\Repository\VertexRepository;
use Collator;
use DateInterval;
use IteratorIterator;
use MongoDB\BSON\ObjectId;
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
            protected AlgorithmClient $algorithm)
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
     * Divides a big graph into smaller partitions. 
     * Each partition is centered around one vertex (aka the focus point) of the given category.
     * The criterion to distribute others vertices in each partition is based on the distance of the vertex from the focus point.
     * The closest focus point "owns" that vertex. If that vertex is at the same distance from multiple focus point, it is duplicated
     * @param Digraph $graph
     * @param string $category
     * @return array an array indexed by the title of those focus points (vertices of the given category) of arrays of GraphVertex
     */
    public function getPartitionByDistanceFromCategory(Digraph $graph, string $category): array
    {
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

        // distance matrix computed by Floyd-Warshall algorithm
        $this->algorithm->floydWarshall($matrix);

        // initialize partitions
        $partition = [];
        $timeline = $graph->extractVectorByCategory($category);
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
    public function getPartitionByTimeline(): array
    {
        $graph = $this->repository->loadGraph();
        return $this->getPartitionByDistanceFromCategory($graph, 'timeline');
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

    protected function createGraphVertexFor(\App\Entity\Vertex $vertex): GraphVertex
    {
        return new GraphVertex([
            '_id' => (string) $vertex->getPk(),
            'title' => $vertex->getTitle(),
            '__pclass' => get_class($vertex)
        ]);
    }

    /**
     * Gets a partition for a given timeline, sorted by betweenness centrality (Brandes algorithm)
     * @param Timeline $timeline
     * @return array an array per category of arrays of GraphVertex sorted by betweenness centrality weights
     */
    public function getVertexSortedByCentrality(Timeline $timeline): array
    {
        $graph = $this->repository->loadGraph();
        $partition = $this->getPartitionByDistanceFromCategory($graph, 'timeline')[$timeline->getTitle()];
        // the partition par timeline with floyd-warshall excludes the timeline itself (and it's a good thing)
        // but when it comes to betweenness, we need this vertex, therefore it is added to the partition
        // for the Brandes algorithm :
        array_unshift($partition, $this->createGraphVertexFor($timeline));

        $pk2idx = array_flip(array_keys($graph->vertex));
        $matrix = [];
        // we scan the subset and fill an unidrected adjacency matrix with the full graph adjacency matrix
        foreach ($partition as $row => $source) {
            foreach ($partition as $col => $target) {
                // gets indicies from the full graph
                $s = $pk2idx[$source->pk];
                $t = $pk2idx[$target->pk];
                // with the original indicies, we can fill the subset matrix at [row][col]
                $matrix[$row][$col] = $graph->adjacency[$s][$t] || $graph->adjacency[$t][$s];
            }
        }

        $between = $this->algorithm->brandesCentrality($matrix);

        // creates an array to store all info in vertices for the movie poster
        $moviePoster = [];
        foreach ($between as $idx => $weight) {
            /** @var GraphVertex $vertex */
            $vertex = $partition[$idx];
            $vertex->betweenness = $weight;
            $moviePoster[$vertex->pk] = $vertex;
        }

        // loads vertices content to extract pictures
        $pk = array_map(function ($val) {
            return new ObjectId($val);
        }, array_keys($moviePoster));

        $iter = $this->repository->search(['_id' => ['$in' => $pk]]);
        foreach ($iter as $v) {
            $moviePoster[(string) $v->getPk()]->picture = $v->extractPicture();
        }

        usort($moviePoster, function (GraphVertex $a, GraphVertex $b) {
            return $b->betweenness <=> $a->betweenness;
        });

        return $moviePoster;
    }

}
