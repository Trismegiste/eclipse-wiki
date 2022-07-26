<?php

/*
 * eclipse-wiki
 */

namespace App\Entity\Wfc;

/**
 * The value of the wave function at a precise hexagon
 */
class WaveCell
{

    protected $tileSuperposition = []; // list of possible EigenTile
    public $updated = false;

    public function __construct(array $eigenTileBase)
    {
        $this->tileSuperposition = $eigenTileBase;
    }

    public function getEntropy(): int
    {
        return count($this->tileSuperposition);
    }

    public function collapse(): void
    {
        $keys = array_keys($this->tileSuperposition);
        $n = random_int(0, count($this->tileSuperposition) - 1);
        $this->setEigenState($this->tileSuperposition[$keys[$n]]);
    }

    public function setEigenState(EigenTile $tile): void
    {
        $this->tileSuperposition = [$tile->getUniqueId() => $tile];
    }

    public function getNeighbourEigenTile(int $direction): array
    {
        $neighbour = [];
        foreach ($this->tileSuperposition as $tile) {
            /** @var \App\Entity\Wfc\EigenTile $tile */
            $neighbour = array_merge($neighbour, $tile->neighbourList[$direction]);
        }

        return $neighbour;
    }

    public function interactWith(array $eigenTile): void
    {
        if (!count($eigenTile)) { // to prevent error to propagate
            return;
        }

        $this->tileSuperposition = array_intersect_key($this->tileSuperposition, $eigenTile);
    }

    public function getFirst(): EigenTile
    {
        $idx = array_key_first($this->tileSuperposition);

        return $this->tileSuperposition[$idx];
    }

}
