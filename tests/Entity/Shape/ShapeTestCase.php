<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Entity\Shape;

use App\Entity\Shape\Strategy;
use App\Voronoi\MapDrawer;
use App\Voronoi\SquareGrid;
use PHPUnit\Framework\TestCase;

abstract class ShapeTestCase extends TestCase
{

    protected Strategy $sut;
    protected $map;
    protected $drawer;

    abstract protected function createShape(): Strategy;

    protected function setUp(): void
    {
        $this->sut = $this->createShape();
        $this->map = $this->createMock(SquareGrid::class);
        $this->drawer = new MapDrawer();
        $this->drawer->setMap($this->map);

        $this->map->expects($this->any())
                ->method('getSize')
                ->willReturn(20);
    }

    public function testName()
    {
        $this->assertIsString($this->sut->getName());
    }

    protected function draw()
    {
        $this->sut->draw($this->drawer);
    }

}
