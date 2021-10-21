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

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function setEdges(array $listing)
    {
        $this->edges = $listing;
    }

}
