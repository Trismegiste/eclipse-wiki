<?php

/*
 * eclipse-wiki
 */

use App\Entity\Shape\Border;
use App\Entity\Shape\Strategy;
use App\Tests\Entity\Shape\ShapeTestCase;

class BorderTest extends ShapeTestCase
{

    protected function createShape(): Strategy
    {
        return new Border();
    }

    public function testBorder()
    {
        $this->map->expects($this->once())
                ->method('getSize')
                ->willReturn(20);

        $this->map->expects($this->exactly(80))
                ->method('setCell');

        $this->draw();
    }

}
