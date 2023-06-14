<?php

/*
 * Eclipse Wiki
 */

use App\Entity\PlotNode;
use App\Entity\Timeline;
use PHPUnit\Framework\TestCase;

class TimelineTest extends TestCase
{

    protected Timeline $sut;

    protected function setUp(): void
    {
        $this->sut = new Timeline('Scenar');
    }

    public function testFlattenTree()
    {
        $this->sut->setTree(new PlotNode('Root', [
                    new PlotNode('Act 1', [new PlotNode('Scene 1.1'), new PlotNode('Scene 1.2', [new PlotNode('Event 1.2.1')])]),
                    new PlotNode('Act 2'),
                    new PlotNode('Act 3', [new PlotNode('Scene 3.1')]),
        ]));

        $dump = $this->sut->bsonSerialize();
        var_dump($dump['content']);
    }

}
