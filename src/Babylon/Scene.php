<?php

namespace App\Babylon;

use App\Voronoi\HexaMap;

class Scene implements \JsonSerializable
{

    protected HexaMap $wrapped;

    public function __construct(HexaMap $map)
    {
        $this->wrapped = $map;
    }

    public function jsonSerialize(): array
    {
        $ground = [];
        $side = $this->wrapped->getSize();
        for ($x = 0; $x < $side; $x++) {
            for ($y = 0; $y < $side; $y++) {
                $cell = $this->wrapped->getCell([$x, $y]);
                $ground[] = [
                    'x' => $this->wrapped->getAbscissa($x, $y),
                    'y' => $y,
                    'content' => $cell
                ];
            }
        }

        return [
            'theme' => 'habitat',
            'side' => $this->wrapped->getSize(),
            'grid' => $ground,
            'npcToken' => $this->wrapped->getNpcToken(),
            'wallHeight' => 1.5,
            'texture' => ['default', 'cluster', 'void', 'cluster-sleep', 'cluster-energy', 'cluster-neutral', 'cluster-industry', 'cluster-park', 'cluster-entertainment', 'cluster-oxygen']
        ];
    }

}
