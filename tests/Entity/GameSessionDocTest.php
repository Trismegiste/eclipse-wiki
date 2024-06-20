<?php

/*
 * eclipse-wiki
 */

use App\Entity\GameSessionDoc;
use App\Entity\Loveletter;
use App\Entity\Scene;
use MongoDB\BSON\ObjectId;
use PHPUnit\Framework\TestCase;

class GameSessionDocTest extends TestCase
{

    protected GameSessionDoc $sut;

    protected function setUp(): void
    {
        $this->sut = new GameSessionDoc();
    }

    public function testDefault()
    {
        $this->assertCount(0, $this->sut->getHistory());
        $this->assertFalse($this->sut->hasPinnedTimeline());
    }

    public function testFilter()
    {
        $this->sut->push(new Loveletter('random'));
        $this->assertCount(0, $this->sut->getHistory());
    }

    public function testTimestampedStack()
    {
        $first = new Scene('scene1');
        $first->setPk(new ObjectId());
        $this->sut->push($first);
        sleep(1);
        $second = new Scene('scene2');
        $second->setPk(new ObjectId());
        $this->sut->push($second);

        $history = $this->sut->getHistory();
        $firstKey = array_key_first($history);
        $this->assertEquals('Scene2', $history[$firstKey]['title']);
    }

    public function testPin()
    {
        $timeline = new \App\Entity\Timeline('scenar');
        $timeline->setPk(new ObjectId());

        $this->sut->setTimeline($timeline);
        $this->assertTrue($this->sut->hasPinnedTimeline());
        $this->assertEquals('Scenar', $this->sut->getTimeline()['title']);
        $this->assertNotEmpty($this->sut->getTimeline()['pk']);
    }

}
