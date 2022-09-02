<?php

/*
 * eclipse-wiki
 */

class TileProviderTest extends \PHPUnit\Framework\TestCase
{

    protected App\Repository\TileProvider $sut;

    protected function setUp(): void
    {
        $this->sut = new App\Repository\TileProvider(__DIR__ . '/../Voronoi');
    }

    public function testGetTileSet()
    {
        $it = $this->sut->getTileSet('dummy');
        $this->assertInstanceOf(Iterator::class, $it);
        foreach ($it as $key => $tile) {
            $this->assertStringEndsWith('default.svg', $key);
            $this->assertInstanceOf(App\Voronoi\TileSvg::class, $tile);
        }
    }

}
