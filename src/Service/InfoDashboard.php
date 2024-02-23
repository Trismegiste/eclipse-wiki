<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Repository\VertexRepository;
use DateInterval;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * General information for the dashboard (cached)
 */
class InfoDashboard
{

    const reasonableDelay = '1 hour';

    public function __construct(
            protected CacheInterface $cache,
            protected VertexRepository $repository,
            protected DigraphExplore $digraph,
            protected Storage $storage)
    {
        
    }

    /**
     * Counts all links ( a.k.a [[something]] ) that don't have a page (also know as "redlinks" in MediaWiki slang)
     * @return int
     */
    public function getBrokenLinkCount(): int
    {
        return $this->cache->get('dashboard_broken_link', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString(self::reasonableDelay));
                    return count($this->digraph->searchForBrokenLink());
                });
    }

    /**
     * Counts all pictures ( a.k.a [[file:something.jpg]] ) that misses in the Storage
     * @return int
     */
    public function getBrokenPictureCount(): int
    {
        return $this->cache->get('dashboard_broken_picture', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString(self::reasonableDelay));
                    return count($this->storage->searchForBrokenPicture($this->repository->search()));
                });
    }

    /**
     * Counts all pages that aren't connected to something
     * @return int
     */
    public function getOrphanCount(): int
    {
        return $this->cache->get('dashboard_orphan', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString(self::reasonableDelay));
                    return count($this->digraph->findOrphan());
                });
    }

    /**
     * Counts all vertices in the game, with a distinct count for archived or not
     */
    public function getVertexCount(): int
    {
        return $this->cache->get('dashboard_vertex', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString(self::reasonableDelay));
                    return array_sum(array_column(iterator_to_array($this->repository->countByClass()), 'total'));
                });
    }

}
