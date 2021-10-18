<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Container for a list of Edges
 */
trait EdgeContainer
{

    protected $edges = [];

    public function addEdge(Edge $sk): void
    {
        foreach ($this->edges as $item) {
            if ($item->getName() === $sk->getName()) {
                return;
            }
        }

        $this->edges[] = $sk;
    }

    public function removeEdge(Edge $sk): void
    {
        foreach ($this->edges as $idx => $item) {
            if ($item->getName() === $sk->getName()) {
                unset($this->edges[$idx]);
            }
        }
    }

    public function getEdges(): array
    {
        return $this->edges;
    }

}
