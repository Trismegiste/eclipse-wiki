<?php

/*
 * eclipse-wiki
 */

use App\Entity\CreationTree\Graph;
use App\Entity\CreationTree\Node;
use PHPUnit\Framework\TestCase;

class GraphTest extends TestCase
{

    protected Graph $sut;

    protected function setUp(): void
    {
        $this->sut = new Graph();
    }

    public function testJson()
    {
        $this->sut->node[] = new Node('root');

        $whatJsWillSee = json_decode(json_encode($this->sut), true);
        $this->assertCount(1, $whatJsWillSee);
        $this->assertArrayHasKey('name', $whatJsWillSee[0]);
        $this->assertEquals('root', $whatJsWillSee[0]['name']);
    }

}
