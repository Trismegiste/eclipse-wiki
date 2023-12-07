<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

use App\Repository\VertexRepository;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * General information for the dashboard (cached)
 */
class InfoDashboard
{

    public function __construct(
            protected CacheInterface $cache,
            protected VertexRepository $repository,
            protected DigraphExplore $digraph,
            protected Storage $storage)
    {
        
    }

    public function getBrokenLinkCount(): int
    {
        return count($this->digraph->searchForBrokenLink());
    }

    public function getBrokenPictureCount(): int
    {
        return count($this->storage->searchForBrokenPicture($this->repository->search()));
    }

    public function getOrphanCount(): int
    {
        return count($this->digraph->findOrphan());
    }

    public function getVertexCount(): int
    {
        return array_sum(array_column(iterator_to_array($this->repository->countByClass()), 'total'));
    }

}
