<?php

/*
 * eclipse-wiki
 */

use App\Voronoi\TileSvg;
use PHPUnit\Framework\TestCase;

class TileSvgTest extends TestCase
{

    protected TileSvg $sut;

    protected function setUp(): void
    {
        $this->sut = new TileSvg();
    }

    public function testLoad()
    {
        $ret = $this->sut->load(__DIR__ . '/default.svg');
        $this->assertTrue($ret);
    }

    public function testKey()
    {
        $this->sut->load(__DIR__ . '/default.svg');
        $tile = $this->sut->getTile();
        $this->assertEquals('default', $this->sut->getKey());
    }

    public function testGetTile()
    {
        $this->sut->load(__DIR__ . '/default.svg');
        $tile = $this->sut->getTile();
        $this->assertInstanceOf(DOMElement::class, $tile);
    }

}
