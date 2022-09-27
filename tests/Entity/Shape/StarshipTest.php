<?php

use App\Entity\Shape\Starship;
use App\Entity\Shape\Strategy;
use App\Tests\Entity\Shape\ShapeTestCase;

class StarshipTest extends ShapeTestCase
{

   protected function createShape(): Strategy
    {
        return new Starship();
    }

    public function testDraw()
    {
        $this->map->expects($this->exactly(253))
                ->method('setCell');
        $this->draw();
    }

}

