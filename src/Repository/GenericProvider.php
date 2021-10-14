<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Indexable;

/**
 * GenericProvider provides Indexable
 */
interface GenericProvider
{

    /**
     * Gets all Indexable
     * @return array
     */
    public function getListing(): array;

    /**
     * Gets one Indexable with its unique key
     * @param string $key
     * @return Indexable
     */
    public function findOne(string $key): Indexable;
}
