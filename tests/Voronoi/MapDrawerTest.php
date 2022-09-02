<?php

/*
 * eclipse-wiki
 */

use App\Voronoi\HexaCell;
use App\Voronoi\HexaMap;
use App\Voronoi\MapDrawer;
use PHPUnit\Framework\TestCase;

class MapDrawerTest extends TestCase
{

    protected MapDrawer $sut;
    protected $map;

    protected function setUp(): void
    {
        $this->map = $this->createMock(HexaMap::class);
        $this->map->expects($this->any())
                ->method('getSize')
                ->willReturn(20);

        $this->sut = new MapDrawer($this->map);
    }

    public function testHCross()
    {
        $this->map->expects($this->exactly(2 * 20))
                ->method('setCell');
        $this->sut->horizontalCross($this->createStub(HexaCell::class), 1, true);
    }

    public function testVCross()
    {
        $this->map->expects($this->exactly(2 * 20))
                ->method('setCell');
        $this->sut->verticalCross($this->createStub(HexaCell::class), 1, true);
    }

    public function testDrawFrame()
    {
        $this->map->expects($this->exactly(4 * 20))
                ->method('setCell');
        $this->sut->drawFrame($this->createStub(HexaCell::class));
    }

    public function testCircle()
    {
        $this->map->expects($this->exactly(88))  // 2 * 0.75 * 10 * PI * ~1.5 on average
                ->method('setCell');
        $this->sut->circle($this->createStub(HexaCell::class));
    }

    public function testDisk()
    {
        $this->map->expects($this->exactly(151))
                ->method('setCell');
        $this->sut->drawCircleContainer($this->createStub(HexaCell::class));
    }

    public function testTore()
    {
        $this->map->expects($this->exactly(220))
                ->method('setCell');
        $this->sut->drawTorusContainer($this->createStub(HexaCell::class));
    }

    public function testSize()
    {
        $this->assertEquals(20, $this->sut->getCanvasSize());
    }

}
