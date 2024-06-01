<?php

/*
 * Eclipse Wiki
 */

namespace App\Algebra;

/**
 * An edge in the graph
 */
class GraphEdge
{

    public string $source;
    public string $target;

    public function __construct(array $edge)
    {
        $this->source = $edge['source'];
        $this->target = $edge['target'];
    }

}
