<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Timeline;
use App\Entity\Vertex;
use App\Repository\VertexRepository;
use Collator;
use DateInterval;
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
     * WARNING : highly database-intensive
     * @return array
     */
    public function getAdjacencyMatrix(): array
    {
        // census of vertices
        $vertexPk = [];
        $vertexTitle = [];
        foreach ($this->repository->search() as $vertex) {
            $vertexPk[(string) $vertex->getPk()] = false;
            $vertexTitle[$vertex->getTitle()] = (string) $vertex->getPk();
        }

        // init matrix
        $matrix = [];
        foreach ($vertexPk as $pk => $dummy) {
            $matrix[$pk] = $vertexPk;
        }

        // fill matrix with links
        foreach ($this->repository->search() as $target) {
            $sourceTitle = $this->repository->searchByBacklinks($target->getTitle());
            foreach ($sourceTitle as $source) {
                $sourcePk = $vertexTitle[$source];
                $matrix[$sourcePk][(string) $target->getPk()] = true;
            }
        }

        return $matrix;
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

}
