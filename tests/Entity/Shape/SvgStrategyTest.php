<?php

use App\Entity\Shape\SvgStrategy;
use App\Entity\Shape\Strategy;
use App\Tests\Entity\Shape\ShapeTestCase;

class SvgStrategyTest extends ShapeTestCase
{

   protected function createShape(): Strategy
    {
        return new SvgStrategy('test', '<svg viewBox="0 0 25 25"><circle cx="12" cy="12" r="10" fill="black"/></svg>');
    }

    public function testDraw()
    {
        $this->map->expects($this->exactly(188))  // ~ 20² - pi . 9²
                ->method('setCell');
        $this->draw();
    }

}

