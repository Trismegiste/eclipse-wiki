<?php

/*
 * eclipse-wiki
 */

namespace App\Entity;

use App\Attribute\PlaceIcon;

/**
 * Stage scene
 */
#[PlaceIcon('place')]
class Place extends Vertex
{

    public $world;
    public $temperature;
    public $pressure;
    public $gravity;
    public ?string $youtubeUrl = null;
    public ?string $battlemap3d = null;
    public ?MapConfig $voronoiParam = null;

    /**
     * Gets all legends (spot on battlemap) in the content
     * @return array array of LegendSpot
     */
    public function extractLegendSpot(): array
    {
        if (is_null($this->content)) {
            return [];
        }

        $matches = [];
        preg_match_all('#\{\{legend\s*\|\s*([^\|]+)\s*\|\s*(\d+)\s*\}\}#', $this->getContent(), $matches, PREG_SET_ORDER, 0);
        $spot = [];
        foreach ($matches as $item) {
            $spot[] = new LegendSpot(trim($item[1]), $item[2]);
        }

        return $spot;
    }

}
