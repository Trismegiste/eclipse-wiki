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

    const FILENAME = "quick-creation.json";

    protected string $pathname;

    public function __construct(\App\Service\Storage $storage)
    {
        $this->pathname = join_paths($storage->getRootDir(), self::FILENAME);
    }

    public function load(): array
    {
        if (!file_exists($this->pathname)) {
            return [];
        }

        return \MongoDB\BSON\toPHP(\MongoDB\BSON\fromJSON(file_get_contents($this->pathname)), ['root' => 'array']);
    }

    public function save(array $nodes): void
    {
        file_put_contents($this->pathname, \MongoDB\BSON\toJSON(\MongoDB\BSON\fromPHP(array_values($nodes))));
    }

}
