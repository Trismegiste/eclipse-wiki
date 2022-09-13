<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Entity\Shape;

use App\Entity\Shape\Strategy;
use App\Voronoi\HexaMap;
use App\Voronoi\MapDrawer;
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
        $this->map = $this->createMock(HexaMap::class);
        $this->drawer = new MapDrawer($this->map);
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
