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

    public function testGround()
    {
        ob_start();
        $this->sut->dumpGround(0, 0);
        $fragment = ob_get_clean();
        $this->assertStringContainsString('<use', $fragment);
        $this->assertStringContainsString('#toto', $fragment);
    }

    public function testNoDoor()
    {
        ob_start();
        $this->sut->dumpDoor(0, 0);
        $fragment = ob_get_clean();
        $this->assertStringNotContainsString('<use', $fragment);
        $this->assertStringNotContainsString('#eastdoor', $fragment);
    }

    public function testDoor()
    {
        $this->sut->door[HexaCell::EAST] = true;
        ob_start();
        $this->sut->dumpDoor(0, 0);
        $fragment = ob_get_clean();
        $this->assertStringContainsString('<use', $fragment);
        $this->assertStringContainsString('#eastdoor', $fragment);
    }

    public function testNoWall()
    {
        ob_start();
        $this->sut->dumpDoor(0, 0);
        $fragment = ob_get_clean();
        $this->assertStringNotContainsString('<use', $fragment);
        $this->assertStringNotContainsString('#eastwall', $fragment);
    }

    public function testWall()
    {
        $this->sut->wall[HexaCell::EAST] = true;
        ob_start();
        $this->sut->dumpWall(0, 0);
        $fragment = ob_get_clean();
        $this->assertStringContainsString('<use', $fragment);
        $this->assertStringContainsString('#eastwall', $fragment);
    }

    public function testLegend()
    {
        ob_start();
        $this->sut->dumpLegend('yolo', 0, 0);
        $fragment = ob_get_clean();
        $this->assertStringContainsString('<text', $fragment);
        $this->assertStringContainsString('yolo', $fragment);
    }

}
