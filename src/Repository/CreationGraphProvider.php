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

    const FILE = "/www/database/quick-creation.json";

    public function load(): array
    {
        return \MongoDB\BSON\toPHP(\MongoDB\BSON\fromJSON(file_get_contents(self::FILE)), ['root' => 'array']);
    }

    public function save(array $nodes): void
    {
        file_put_contents(self::FILE, \MongoDB\BSON\toJSON(\MongoDB\BSON\fromPHP($nodes)));
    }

}
