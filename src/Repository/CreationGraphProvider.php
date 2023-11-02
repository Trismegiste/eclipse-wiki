<?php

/*
 * Eclipse Wiki
 */

namespace App\Repository;

use App\Entity\CreationTree\Graph;
use App\Service\Storage;
use function join_paths;

/**
 * Provider for the creation graph
 */
class CreationGraphProvider
{

    const FILENAME = "quick-creation.graph";

    protected string $pathname;

    public function __construct(Storage $storage)
    {
        $this->pathname = join_paths($storage->getRootDir(), self::FILENAME);
    }

    public function load(): Graph
    {
        $graph = new Graph();

        if (!file_exists($this->pathname)) {
            return $graph;
        }

        $content = \MongoDB\BSON\toPHP(\MongoDB\BSON\fromJSON(file_get_contents($this->pathname)), ['root' => 'array']);
        $graph->node = $content;

        return $graph;
    }

    public function save(Graph $graph): void
    {
        $root = $graph->getNodeByName('root');
        $graph->sortByLevelFromRoot($root);
        file_put_contents($this->pathname, \MongoDB\BSON\toJSON(\MongoDB\BSON\fromPHP(array_values($graph->node))));
    }

}
