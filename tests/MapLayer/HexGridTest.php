<?php

/*
 * eclipse-wiki
 */

use App\MapLayer\HexGrid;
use PHPUnit\Framework\TestCase;
use Trismegiste\MapGenerator\Procedural\GenericAutomaton;

class HexGridTest extends TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $map = $this->createMock(GenericAutomaton::class);
        $map->expects($this->any())
                ->method('getSize')
                ->willReturn(25);

        $this->sut = new HexGrid($map);
    }

    public function testSvg()
    {
        ob_start();
        $this->sut->printSvg();
        $svg = ob_get_clean();
        $this->assertStringContainsString('hexmap', $svg);
    }

}
