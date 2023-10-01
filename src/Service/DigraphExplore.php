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
     * @param int $distance
     * @return array
     */
    public function graphToSortedCategory(Timeline $root, int $distance = 2): array
    {
        return $this->cache->get('tree_extract_' . $root->getPk(), function (ItemInterface $item) use ($root, $distance) {
                    $item->expiresAfter(DateInterval::createFromDateString('5 minute'));
                    $tree = $this->repository->exploreTreeFrom($root, $distance);
                    $intl = new Collator($this->localeParam);

                    $dump = [];
                    // dispatch by category
                    foreach ($tree as $v) {
                        $dump[$v->getCategory()][] = $v->getTitle();
                    }
                    // sort each category
                    foreach ($dump as $key => $v) {
                        $intl->sort($dump[$key]);
                    }

                    return $dump;
                });
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
        foreach ($this->repository->search() as $vertex) {
            $scan = $vertex->getInternalLink();
            foreach ($scan as $target) {
                if (is_null($this->repository->findByTitle($target))) {
                    $keep[$target][$vertex->getTitle()] = true;
                }
            }
        }

        ksort($keep);

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
