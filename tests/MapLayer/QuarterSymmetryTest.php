<?php

/*
 * eclipse-wiki
 */

class QuarterSymmetryTest extends PHPUnit\Framework\TestCase
{

    protected $map;
    protected $sut;

    protected function setUp(): void
    {
        $this->map = new Trismegiste\MapGenerator\Procedural\SpaceStation(25);
        $this->sut = new App\MapLayer\QuarterSymmetry($this->map);
    }

    public function testDuplicate()
    {
        $this->map->set(1, 1);
        $this->sut->duplicate();
        $grid = $this->map->getGrid();
        $this->assertEquals(1, $grid[1][1]);
        $this->assertEquals(1, $grid[23][23]);
        $this->assertEquals(1, $grid[23][1]);
        $this->assertEquals(1, $grid[1][23]);
    }

}
