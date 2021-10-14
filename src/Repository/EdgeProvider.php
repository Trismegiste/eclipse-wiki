<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Indexable;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Provider for Edges
 */
class EdgeProvider extends CachedProvider
{

    public function findOne(string $key): Indexable
    {
        
    }

    public function getListing(): array
    {
        return $this->cache->get('edge_list', function (ItemInterface $item) {
                    $item->expiresAfter(\DateInterval::createFromDateString('1 day'));
                    $edge = $this->wiki->searchPageFromCategory('Atout', 200);

                    $listing = [];
                    foreach ($edge as $row) {
                        $listing[$row->title] = $row->title;
                    }

                    return $listing;
                });
    }

}
