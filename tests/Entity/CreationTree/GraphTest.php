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
        $this->assertArrayHasKey('children', $whatJsWillSee[0]);
        $this->assertEquals('root', $whatJsWillSee[0]['name']);
        $this->assertCount(0, $whatJsWillSee[0]['children']);
    }

    public function testGetParent()
    {
        $p1 = new Node('Anakin');
        $p1->children[] = 'Luke';
        $p2 = new Node('Padme');
        $p2->children[] = 'Luke';

        $child = new Node('Luke');

        $this->sut->node[] = $p1;
        $this->sut->node[] = $p2;
        $this->sut->node[] = $child;

        $found = $this->sut->getParentNode($child);
        $this->assertCount(2, $found);
        $this->assertInstanceOf(Node::class, $found[0]);
        $this->assertInstanceOf(Node::class, $found[1]);
    }

}
