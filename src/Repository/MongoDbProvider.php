<?php

/*
 * eclipse-wiki
 */

namespace App\Repository;

use Trismegiste\Toolbox\MongoDb\Repository;

/**
 * For MongoDb
 */
abstract class MongoDbProvider implements GenericProvider
{

    protected $repository;

    public function __construct(Repository $pageRepo)
    {
        $this->repository = $pageRepo;
    }

}
