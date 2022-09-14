<?php

/*
 * eclipse-wiki
 */

use App\Entity\Shape\Dome;
use App\Entity\Shape\Strategy;
use App\Tests\Entity\Shape\ShapeTestCase;

class DomeTest extends ShapeTestCase
{

    protected function createShape(): Strategy
    {
        return new Dome();
    }

    public function testDomeCircle()
    {
        $this->map->expects($this->exactly(151))  // ~ 20² - 3.14 × 9²
                ->method('setCell');
        $this->draw();
    }

}
