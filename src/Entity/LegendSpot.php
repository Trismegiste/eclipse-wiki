<?php

/*
 * Eclipse-wiki
 */

namespace App\Entity;

/**
 * A legend spot on a battlemap
 */
class LegendSpot {

    public function __construct(protected string $title, protected int $index) {}

    public function getTitle(): string {
        return $this->title;
    }

    public function getIndex(): int {
        return $this->index;
    }

}


