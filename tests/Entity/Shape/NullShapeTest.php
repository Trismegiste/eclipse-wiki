<?php

/*
 * eclipse-wiki
 */

class NullShapeTest extends App\Tests\Entity\Shape\ShapeTestCase
{

    protected function createShape(): \App\Entity\Shape\Strategy
    {
        return new App\Entity\Shape\NullShape();
    }

    public function testNoDraw()
    {
        $this->map->expects($this->never())->method('setCell');
        $this->draw();
    }

}
