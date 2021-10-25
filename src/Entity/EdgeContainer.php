<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

/**
 * Container for a list of Edges & Hindrances
 */
trait EdgeContainer
{

    protected $edges = [];
    protected $hindrances = [];

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function setEdges(array $listing)
    {
        $this->edges = $listing;
    }

    public function getHindrances(): array
    {
        return $this->hindrances;
    }

    public function setHindrances(array $listing)
    {
        $this->hindrances = $listing;
    }

}
