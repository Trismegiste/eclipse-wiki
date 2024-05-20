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

    protected Repository $repository;
    protected CacheInterface $cache;
    protected string $localeParam;

    public function __construct(VertexRepository $repo, CacheInterface $cache, string $locale)
    {
        $this->repository = $repo;
        $this->cache = $cache;
        $this->localeParam = $locale;
    }

    /**
     * Explores the digraph from a Timeline node and stop until another Timeline or after a given distance
     * @return array
     */
    public function graphToSortedCategory(Timeline $root): array
    {
        //   return $this->cache->get('tree_extract_' . $root->getPk(), function (ItemInterface $item) use ($root, $distance) {
        // $item->expiresAfter(DateInterval::createFromDateString('5 minute'));
        $partition = $this->getPartitionByTimeline()[$root->getTitle()];
        $intl = new Collator($this->localeParam);

        $dump = [];
        // dispatch by category
        foreach ($partition as $v) {
            $dump[$v['category']][] = $v['title'];
        }
        // sort each category
        foreach ($dump as $key => $v) {
            $intl->sort($dump[$key]);
        }

        return $dump;
        //       });
    }

    public function getPartitionByTimeline(): array
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

        // dump minimal info for vertices
        $timelineIdx = [];
        $title = [];
        $category = [];
        foreach ($this->repository->search() as $vertex) {
            $pk = (string) $vertex->getPk();
            $title[$pk] = $vertex->getTitle();
            $category[$pk] = $vertex->getCategory();
            if ($vertex instanceof \App\Entity\Timeline) {
                $timelineIdx[] = $invertAssocId[$pk];
            }
        }

        // group vertices by closest Timeline. If a vertex is closest to multiple Timeline, it is duplicated
        $partition = [];
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
            // append the vertex to all Timeline at the closest distance
            foreach ($distanceToTimeline as $found => $dist) {
                if ($minDist === $dist) {
                    $partition[$title[$assocId[$found]]][] = [
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
        // absolutely NOT optimized algorithm
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
