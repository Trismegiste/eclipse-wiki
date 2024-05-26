<?php

/*
 * Eclipse Wiki
 */

namespace App\Entity;

/**
 * A sub-graph in a larger graph
 * Focus on one Vertex
 */
class Subgraph
{

    public function __construct(protected Vertex $focus, protected array $inbound = [])
    {
        
    }

    public function getFocus(): Vertex
    {
        return $this->focus;
    }

    public function appendInbound(Vertex $v): void
    {
        $this->inbound[] = $v;
    }

    public function getInbound(): array
    {
        return $this->inbound;
    }

}
