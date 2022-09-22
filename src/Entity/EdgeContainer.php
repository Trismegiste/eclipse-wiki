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

    protected array $edges = [];
    protected array $hindrances = [];

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function setEdges(array $listing): void
    {
        $this->edges = $listing;
    }

    public function getHindrances(): array
    {
        return $this->hindrances;
    }

    public function setHindrances(array $listing): void
    {
        $this->hindrances = $listing;
    }

}
