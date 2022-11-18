<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Entity\Timeline;
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

    public function findOrphan(): array
    {
        $outbound = [];
        foreach ($this->repository->search() as $vertex) {
            /** @var \App\Entity\Vertex $vertex */
            $source = $vertex->getTitle();
            $outbound[$source] = [];
            $target = $vertex->getInternalLink();
            foreach ($target as $title) {
                $outbound[$source][$title] = true;  // prevent duplicates
            }
        }

        // now we have an outbound sparse matrix (source, target), lets build an inbound sparse matrix
        $inbound = [];
        foreach ($outbound as $source => $links) {
            foreach ($links as $title => $dummy) {
                $inbound[$title][$source] = true;
            }
        }

        // lets find all orphans : no outbound nor inbound vertex
        $orphan = [];
        foreach ($outbound as $vertex => $links) {
            if ((0 === count($links)) && !key_exists($vertex, $inbound)) {
                $orphan[] = $vertex;
            }
        }

        return $orphan;
    }

}
