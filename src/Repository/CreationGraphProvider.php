<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

/**
 * Provider for the creation graph
 */
class CreationGraphProvider
{

    public function load(): array
    {
        return \MongoDB\BSON\toPHP(\MongoDB\BSON\fromJSON(file_get_contents("/www/database/quick-creation.json")), ['root' => 'array']);
    }

    public function save(array $nodes): void
    {
        
    }

}
