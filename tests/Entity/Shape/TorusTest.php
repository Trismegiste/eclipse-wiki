<?php

/*
 * eclipse-wiki
 */

use App\Entity\Shape\Strategy;
use App\Entity\Shape\Torus;
use App\Tests\Entity\Shape\ShapeTestCase;

class TorusTest extends ShapeTestCase
{

    protected function createShape(): Strategy
    {
        return new Torus();
    }

    public function testDomeCircle()
    {
        $this->map->expects($this->exactly(308))
                ->method('setCell');
        $this->draw();
    }

}
