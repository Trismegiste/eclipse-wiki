<?php

/*
 * eclipse-wiki
 */

class AxialSymmetryTest extends PHPUnit\Framework\TestCase
{

    protected $map;
    protected $sut;

    protected function setUp(): void
    {
        $this->map = new Trismegiste\MapGenerator\Procedural\SpaceStation(25);
        $this->sut = new App\MapLayer\AxialSymmetry($this->map);
    }

    public function testDuplicate()
    {
        $this->map->set(3, 3);
        $this->sut->duplicate();
        $grid = $this->map->getGrid();
        $this->assertEquals(1, $grid[3][3]);
        $this->assertEquals(1, $grid[21][3]); // because 25 squares => 0 to 24 => since 0 -> 24 therefore 3 -> 24-3 = 21
    }

}
