<?php

/*
 * eclipse-wiki
 */

use App\Voronoi\HexaCell;
use App\Voronoi\HexaMap;
use PHPUnit\Framework\TestCase;

class HexaMapTest extends TestCase
{

    protected HexaMap $sut;

    protected function setUp(): void
    {
        $this->sut = new HexaMap(20);
        $this->sut->setCell([10, 10], new HexaCell(666));
    }

    public function testSize()
    {
        $this->assertEquals(20, $this->sut->getSize());
    }

    public function testGetCell()
    {
        $this->assertInstanceOf(HexaCell::class, $this->sut->getCell([10, 10]));
    }

    public function testNeighbour()
    {
        $neigh = $this->sut->getNeighbourCell(10, 10);
        $this->assertCount(6, $neigh);
        foreach ($neigh as $cell) {
            $this->assertNull($cell);
        }
    }

    public function testOneGrowingIteration()
    {
        $this->sut->iterateNeighbourhood();
        $neigh = $this->sut->getNeighbourCell(10, 10);
        $this->assertCount(6, $neigh);
        foreach ($neigh as $cell) {
            $this->assertInstanceOf(HexaCell::class, $cell);
            $this->assertEquals(666, $cell->uid);
        }
    }

    public function testFilling()
    {
        while ($this->sut->iterateNeighbourhood() > 0);

        $counter = 0;
        for ($x = 0; $x < $this->sut->getSize(); $x++) {
            for ($y = 0; $y < $this->sut->getSize(); $y++) {
                $cell = $this->sut->getCell([$x, $y]);
                if (($cell instanceof HexaCell) && (666 === $cell->uid)) {
                    $counter++;
                }
            }
        }
        $this->assertEquals(400, $counter);
    }

    public function testAbscissa()
    {
        $this->assertEqualsWithDelta($this->sut->getAbscissa(10, 10), $this->sut->getAbscissa(10, 12), 1e-7);
        // each column of hexagons "zig and zag"
        $this->assertNotEqualsWithDelta($this->sut->getAbscissa(10, 10), $this->sut->getAbscissa(10, 11), 1e-7);
    }

    public function testWallAndDoor()
    {
        while ($this->sut->iterateNeighbourhood() > 0);
        $this->sut->setCell([10, 10], new HexaCell(111));
        $this->sut->wallProcessing();

        $cell = $this->sut->getCell([10, 10]);
        for ($direction = 0; $direction < 6; $direction++) {
            $this->assertTrue($cell->wall[$direction]);
        }

        $neighb = $this->sut->getCell([9, 10]);
        $this->assertTrue($neighb->wall[0]);

        // we scan the neighbours to find a door because they are reached by the double-loop BEFORE the center cell
        $doorCount = 0;
        foreach ($this->sut->getNeighbourCell(10, 10) as $direction => $neighb) {
            if ($neighb->door[($direction + 3) % 6]) {
                $doorCount++;
            }
        }
        $this->assertEquals(1, $doorCount);
    }

    public function testErosion()
    {
        // fill and with default
        while ($this->sut->iterateNeighbourhood() > 0);
        // create a little room at the center
        $this->sut->getCell([10, 10])->uid = 111;
        foreach ($this->sut->getNeighbourCell(10, 10) as $neighb) {
            $neighb->uid = 111;
        }

        // erosion of the little room
        $this->sut->erodeWith(new HexaCell(7), 6);

        // check new UID after erosion
        foreach ([7 => 666, 7, 7, 111, 7, 7, 666] as $x => $uid) {
            $this->assertEquals($uid, $this->sut->getCell([$x, 10])->uid);
        }
    }

    public function testAllDump()
    {
        while ($this->sut->iterateNeighbourhood() > 0);
        $this->sut->erodeWith(new HexaCell(7), 6); // borders are eroded
        $this->sut->wallProcessing();

        ob_start();
        $this->sut->dumpGround();
        $this->sut->dumpWall();
        $this->sut->dumpDoor();
        $this->sut->dumpLegend();
        $this->sut->dumpFogOfWar();
        $fragment = ob_get_clean();

        $this->assertStringContainsString('<use', $fragment);
        $this->assertStringContainsString('#eastwall', $fragment);
        $this->assertStringContainsString('#eastdoor', $fragment);
        $this->assertStringContainsString('#default', $fragment);
        $this->assertStringContainsString('#fogofwar', $fragment);
        $this->assertStringContainsString('text-anchor', $fragment);
    }

    public function testStatistics()
    {
        $this->sut->setCell([5, 5], new HexaCell(111));
        while ($this->sut->iterateNeighbourhood() > 0);

        $stat = $this->sut->getStatistics();
        $this->assertEquals(2, $stat['default']['rooms']);
    }

    public function testTexturingWithWeights()
    {
        $this->sut->setCell([5, 5], new HexaCell(111));
        while ($this->sut->iterateNeighbourhood() > 0);

        $this->sut->texturing(['tile' => 1], []);
        $stat = $this->sut->getStatistics();
        $this->assertArrayHasKey('tile', $stat);
        $this->assertEquals(2, $stat['tile']['rooms']);
    }

}
