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

    public function getComplexTree()
    {
        return [[
        new PlotNode('Root', [
            new PlotNode('Act 1', [new PlotNode('Scene 1.1'), new PlotNode('Scene 1.2', [new PlotNode('Event 1.2.1')])]),
            new PlotNode('Act 2'),
            new PlotNode('Act 3', [new PlotNode('Scene 3.1')]),
                ])
        ]];
    }

    /** @dataProvider getComplexTree */
    public function testFlattenTree($tree)
    {
        $this->sut->setTree($tree);
        $this->sut->elevatorPitch = 'pitch';

        $dump = $this->sut->bsonSerialize();
        $this->assertStringStartsWith('==Elevator', $dump['content']);
        $this->assertStringEndsWith("Debriefing==\n", $dump['content']);
    }

    /** @dataProvider getComplexTree */
    public function testSerialization($tree)
    {
        $this->sut->setTree($tree);
        $this->assertStringStartsWith('{"data":{"title":"Root"', json_encode($this->sut->getTree()));
    }

    /** @dataProvider getComplexTree */
    public function testRenameLinkInElevatorPitch($tree)
    {
        $this->sut->setTree($tree);
        $this->sut->elevatorPitch = '[[épisseur|morphe]]';
        $this->sut->renameInternalLink('Épisseur', 'ülyss');
        $this->assertEquals('[[ülyss|morphe]]', $this->sut->elevatorPitch);
    }

    /** @dataProvider getComplexTree */
    public function testRenameLinkInTree($tree)
    {
        $this->sut->elevatorPitch = 'pitch';
        $tree->nodes[] = new PlotNode('[[épisseur|morphe]]');
        $this->sut->setTree($tree);

        $this->sut->renameInternalLink('Épisseur', 'ülyss');
        $this->assertEquals('[[ülyss|morphe]]', $this->sut->getTree()->nodes[3]->title);
    }

}
