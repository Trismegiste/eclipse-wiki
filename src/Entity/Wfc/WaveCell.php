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
        $n = random_int(0, count($this->tileSuperposition) - 1);
        $this->tileSuperposition = [$this->tileSuperposition[$n]];
    }

    public function setEigenState(EigenTile $tile): void
    {
        $this->tileSuperposition = [$tile];
    }

    public function getNeighbourEigenTile(int $direction): array
    {
        $neighbour = [];
        foreach ($this->tileSuperposition as $tile) {
            /** @var \App\Entity\Wfc\EigenTile $tile */
            foreach ($tile->neighbourList[$direction] as $constraint) {
                $neighbour[spl_object_id($constraint)] = $constraint;
            }
        }

        return array_values($neighbour);
    }

    public function getNewState(array $eigenTile): array
    {
        $intersect = [];
        foreach ($eigenTile as $newConstraint) {
            if (in_array($newConstraint, $this->tileSuperposition)) {
                $intersect[] = $newConstraint;
            }
        }

        return $intersect;
    }

    public function getFirst(): EigenTile
    {
        return $this->tileSuperposition[0];
    }

}
