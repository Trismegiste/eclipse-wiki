<?php

namespace App\Babylon;

use App\Voronoi\HexaMap;

class Battlemap implements \JsonSerializable
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
        for($x=0; $x<$side; $x++) {
            for($y=0; $y<$side; $y++) {
                $cell = $this->wrapped->getCell([$x,$y]);
                $ground[] = [
                    'x' => $this->wrapped->getAbscissa($x,$y),
                    'y' => $y,
                    'template' => $cell->template
                ];
            }
        }

        return ['ground'=>$ground];
    }

}
