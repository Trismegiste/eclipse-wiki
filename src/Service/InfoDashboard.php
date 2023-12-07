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

    public function getBrokenLinkCount(): int
    {
        return $this->cache->get('dashboard_broken_link', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString(self::reasonableDelay));
                    return count($this->digraph->searchForBrokenLink());
                });
    }

    public function getBrokenPictureCount(): int
    {
        return $this->cache->get('dashboard_broken_picture', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString(self::reasonableDelay));
                    return count($this->storage->searchForBrokenPicture($this->repository->search()));
                });
    }

    public function getOrphanCount(): int
    {
        return $this->cache->get('dashboard_orphan', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString(self::reasonableDelay));
                    return count($this->digraph->findOrphan());
                });
    }

    public function getVertexCount(): int
    {
        return $this->cache->get('dashboard_vertex', function (ItemInterface $item) {
                    $item->expiresAfter(DateInterval::createFromDateString(self::reasonableDelay));
                    return array_sum(array_column(iterator_to_array($this->repository->countByClass()), 'total'));
                });
    }

}
