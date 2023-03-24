<?php

/*
 * eclipse-wiki
 */

use App\Voronoi\HexaCell;
use PHPUnit\Framework\TestCase;

class HexaCellTest extends TestCase
{

    protected HexaCell $sut;

    protected function setUp(): void
    {
        $this->sut = new HexaCell(123, 'toto', true);
    }

    public function testUniqueId()
    {
        $this->assertEquals(123, $this->sut->uid);
    }

    public function testTemplate()
    {
        $this->assertEquals('toto', $this->sut->template);
    }

    public function testGrowable()
    {
        $this->assertTrue($this->sut->growable);
    }

    public function testDoorAndWall()
    {
        $this->assertCount(6, $this->sut->wall);
        $this->assertCount(6, $this->sut->door);
    }

}
