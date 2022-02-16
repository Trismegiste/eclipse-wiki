<?php

/*
 * eclipse-wiki
 */

class RoomColorTest extends \PHPUnit\Framework\TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $map = $this->createMock(Trismegiste\MapGenerator\Procedural\GenericAutomaton::class);
        $map->expects($this->any())
                ->method('getSquaresPerRoomPerLevel')
                ->willReturn([1 => [[['x' => 10, 'y' => 10]]]]);

        $this->sut = new App\MapLayer\RoomColor($map);
    }

    public function testEmptySvg()
    {
        $this->assertEmpty($this->getSvgContent());
    }

    private function getSvgContent(): string
    {
        ob_start();
        $this->sut->printSvg();
        return ob_get_clean();
    }

    public function testGenerate()
    {
        $this->sut->generate(['blue' => 1]);
        $svg = $this->getSvgContent();
        $this->assertStringStartsWith('<g', $svg);
        $this->assertStringContainsString('blue', $svg);
        $this->assertStringContainsString('<rect x="10" y="10"', $svg);
    }

}
