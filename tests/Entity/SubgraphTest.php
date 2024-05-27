<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Entity;

use App\Entity\Freeform;
use App\Entity\Handout;
use App\Entity\Loveletter;
use App\Entity\PlotNode;
use App\Entity\Scene;
use App\Entity\Subgraph;
use App\Entity\Timeline;
use PHPUnit\Framework\TestCase;

class SubgraphTest extends TestCase
{

    protected Subgraph $sut;

    protected function setUp(): void
    {
        $this->sut = new Subgraph(new Timeline('Scenar'));
    }

    public function testGetFocus()
    {
        $this->assertInstanceOf(Timeline::class, $this->sut->getFocus());
    }

    public function testAll()
    {
        $this->assertCount(1, $this->sut->all());
    }

    public function testInbound()
    {
        $this->sut->appendInbound(new Scene('Backlink'));
        $this->assertCount(2, $this->sut->all());
        $this->assertCount(1, $this->sut->getInbound());
    }

    public function testRenameFocus()
    {
        $this->sut->renameFocused('newName');
        $this->assertEquals('NewName', $this->sut->getFocus()->getTitle());
    }

    public function testRenameInbound()
    {
        $focus = new Freeform('Štarkiller');
        $subgraph = new Subgraph($focus);

        $handout = new Handout('BG of Jedi');
        $handout->pcInfo = 'Link to [[štarkiller|Luke]]';
        $handout->gmInfo = 'Link to [[štarkiller|Štarkiller family]]';
        $subgraph->appendInbound($handout);

        $loveletter = new Loveletter('Buy some droids');
        $loveletter->context = '[[štarkiller|Luke]] lived in a farm';
        $loveletter->drama = '[[štarkiller|Luke]] must purchase some droids';
        $subgraph->appendInbound($loveletter);

        $timeline = new Timeline('A new hope');
        $timeline->elevatorPitch = 'Space opera';
        $timeline->setTree(new PlotNode('root', [new PlotNode('Starting with [[štarkiller|Luke]]')]));
        $subgraph->appendInbound($timeline);

        $this->assertCount(4, $subgraph->all());
        $subgraph->renameFocused('skywalker');

        // focus
        $this->assertEquals('Skywalker', $focus->getTitle());
        // handout
        $this->assertEquals('Link to [[Skywalker|Luke]]', $handout->pcInfo);
        $this->assertEquals('Link to [[Skywalker|Štarkiller family]]', $handout->gmInfo);
        // loveletter
        $this->assertEquals('[[Skywalker|Luke]] lived in a farm', $loveletter->context);
        $this->assertEquals('[[Skywalker|Luke]] must purchase some droids', $loveletter->drama);
        // Timeline
        $this->assertEquals('Starting with [[Skywalker|Luke]]', $timeline->getTree()[0]->title);
    }

}
