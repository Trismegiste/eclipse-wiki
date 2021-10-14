<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\Indexable;

/**
 * Description of GenericProvider
 */
interface GenericProvider
{

    public function getListing(): array;

    public function findOne(string $key): Indexable;
}
