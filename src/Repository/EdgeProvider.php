<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use App\Entity\Indexable;

/**
 * Provider for Edges
 */
class EdgeProvider extends MongoDbProvider
{

    public function findOne(string $key): Indexable
    {
        
    }

    public function getListing(): array
    {
        $it = $this->repository->search(['category' => 'Atout']);

        $listing = [];
        foreach ($it as $edge) {
            $listing[$edge->getTitle()] = $edge->getTitle();
        }

        return $listing;
    }

}
