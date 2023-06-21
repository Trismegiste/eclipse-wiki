<?php

use App\Entity\PlotNode;
use PHPUnit\Framework\TestCase;

class PlotNodeTest extends TestCase
{

    protected PlotNode $sut;

    protected function setUp(): void
    {
        $this->sut = new PlotNode('Root');
    }

    public function testOnlyRoot()
    {
        $this->assertArrayNotHasKey(0, $this->sut);
    }

    public function testIterable()
    {
        $this->expectNotToPerformAssertions();
        foreach ($this->sut as $child) {
            $this->assertTrue(false);
        }
    }

    public function testOneChild()
    {
        $this->sut[0] = new PlotNode('Leaf');
        $this->assertArrayHasKey(0, $this->sut);
        foreach ($this->sut as $child) {
            $this->assertInstanceOf(PlotNode::class, $child);
        }
    }

    public function testDefaultSerialize()
    {
        $this->assertEquals('{"data":{"title":"Root","finished":false},"nodes":[]}', json_encode($this->sut));
    }

    public function testSerializeWithChild()
    {
        $this->sut->nodes = [new PlotNode('Child')];
        $this->assertEquals('{"data":{"title":"Root","finished":false},"nodes":[{"data":{"title":"Child","finished":false},"nodes":[]}]}', json_encode($this->sut));
    }

    public function testArrayPush()
    {
        $this->sut[] = new PlotNode('Leaf');
        $this->assertArrayHasKey(0, $this->sut);
    }

}
