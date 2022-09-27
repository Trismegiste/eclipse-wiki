<?php

use App\Entity\Shape\Hexadome;
use App\Entity\Shape\Strategy;
use App\Tests\Entity\Shape\ShapeTestCase;

class HexadomeTest extends ShapeTestCase
{

   protected function createShape(): Strategy
    {
        return new Hexadome();
    }

    public function testDraw()
    {
        $this->map->expects($this->exactly(157))
                ->method('setCell');
        $this->draw();
    }

}

